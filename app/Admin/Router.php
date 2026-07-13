<?php
declare(strict_types=1);

namespace App\Admin;

use App\Core\Config;
use App\Core\Session;

/**
 * Point d'entrée de l'espace membres. Le Kernel appelle `Router::dispatch()` pour tout chemin /admin*.
 * dispatch() émet la réponse complète (en-têtes + corps) puis termine le script.
 */
final class Router
{
    /** Clé d'URL de ressource vers zone de Policy. */
    private const RESOURCES = ['articles' => 'posts', 'equipe' => 'team', 'partenaires' => 'partners', 'comptes' => 'users'];

    public static function dispatch(): void
    {
        $ctx = null;
        try {
            Session::start();
            $ctx = Context::fromGlobals();
            $reply = self::handle($ctx, self::path((string) ($_SERVER['REQUEST_URI'] ?? '/')));
        } catch (\Throwable $e) {
            self::logError($e);
            try {
                $reply = Ui::error($ctx, 500, 'Une erreur est survenue. Réessayez ou prévenez le bureau.');
            } catch (\Throwable) {
                $reply = Reply::text('Erreur interne.', 500);
            }
        }
        self::send($reply);
    }

    /** Chemin relatif à /admin (« » pour la racine, « /articles/3/edit »…). Null si hors /admin. */
    public static function path(string $requestUri, ?string $basePath = null): ?string
    {
        $path = (string) parse_url($requestUri, PHP_URL_PATH);
        $base = rtrim($basePath ?? (string) Config::basePath(), '/');
        if ($base !== '' && str_starts_with($path, $base . '/')) {
            $path = substr($path, strlen($base));
        }
        if ($path === '/admin' || $path === '/admin/') {
            return '';
        }
        if (!str_starts_with($path, '/admin/')) {
            return null;
        }
        return rtrim(substr($path, strlen('/admin')), '/');
    }

    private static function handle(Context $ctx, ?string $path): Reply
    {
        if ($path === null) {
            return Ui::error($ctx, 404, 'Page introuvable.');
        }
        $segs = $path === '' ? [] : explode('/', trim($path, '/'));
        $first = $segs[0] ?? '';
        $rest = array_slice($segs, 1);

        // Jeton CSRF de session sur chaque POST (y compris connexion et installation).
        if ($ctx->isPost() && !$ctx->csrfOk()) {
            return Ui::error($ctx, 403, 'Formulaire expiré ou invalide. Rechargez la page et recommencez.');
        }
        if ($ctx->method !== 'GET' && $ctx->method !== 'POST') {
            return Ui::error($ctx, 405, 'Méthode non autorisée.');
        }

        // Routes publiques.
        if ($first === 'setup' && $rest === []) {
            return AuthController::setup($ctx);
        }
        if ($first === 'login' && $rest === []) {
            return AuthController::login($ctx);
        }

        // Tout le reste exige une session.
        if ($ctx->user === null) {
            return Reply::redirect(Ui::u('/login'));
        }
        $role = $ctx->role();

        if ($first === 'logout' && $rest === []) {
            return $ctx->isPost() ? AuthController::logout($ctx) : Reply::redirect(Ui::u(''));
        }
        if ($first === '' && $rest === []) {
            return DashboardController::index($ctx);
        }
        if ($first === 'profil' && $rest === []) {
            return AuthController::profile($ctx);
        }
        if (isset(self::RESOURCES[$first])) {
            $res = Resource::get($first);
            if ($res === null) {
                return Ui::error($ctx, 404, 'Page introuvable.');
            }
            if (!Policy::allowed($role, self::RESOURCES[$first])) {
                return Ui::error($ctx, 403, 'Cette section est réservée au bureau.');
            }
            return ResourceController::handle($res, $ctx, $rest);
        }
        if ($first === 'candidatures') {
            return self::guard($ctx, 'applications') ?? ApplicationsController::handle($ctx, $rest);
        }
        if ($first === 'messages') {
            return self::guard($ctx, 'messages') ?? MessagesController::handle($ctx, $rest);
        }
        if ($first === 'rgpd' && $rest === []) {
            return self::guard($ctx, 'purge') ?? PurgeController::handle($ctx);
        }
        if ($first === 'modules' && count($rest) === 1) {
            return self::module($ctx, $rest[0]);
        }
        return Ui::error($ctx, 404, 'Page introuvable.');
    }

    private static function guard(Context $ctx, string $area): ?Reply
    {
        return Policy::allowed($ctx->role(), $area) ? null : Ui::error($ctx, 403, 'Cette section est réservée au bureau.');
    }

    private static function module(Context $ctx, string $key): Reply
    {
        $dir = Ui::root() . '/app/Modules';
        $all = ModuleLoader::find($dir, $key);
        if ($all === null) {
            return Ui::error($ctx, 404, 'Module introuvable.');
        }
        if (!in_array($ctx->role(), $all['roles'], true)) {
            return Ui::error($ctx, 403, 'Vous n\'avez pas accès à ce module.');
        }
        $class = $all['controller'];
        $method = $all['method'];
        if (!class_exists($class) || !method_exists($class, $method)) {
            return Ui::error($ctx, 500, 'Module mal configuré.');
        }
        $html = (string) (new $class())->$method($ctx);
        return Ui::wrap($ctx, $all['label'], $html, 'modules/' . $key);
    }

    private static function send(Reply $reply): never
    {
        if (!headers_sent()) {
            http_response_code($reply->status);
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            foreach ($reply->headers as $name => $value) {
                header($name . ': ' . $value);
            }
        }
        if ($reply->file !== null) {
            readfile($reply->file);
        } else {
            echo $reply->body;
        }
        exit;
    }

    private static function logError(\Throwable $e): void
    {
        try {
            $line = gmdate('Y-m-d H:i:s') . ' admin-error ' . get_class($e) . ' ' . basename($e->getFile()) . ':' . $e->getLine() . ' ' . preg_replace('/\s+/', ' ', $e->getMessage()) . "\n";
            @file_put_contents(rtrim((string) Config::root(), '/\\') . '/storage/logs/admin.log', $line, FILE_APPEND | LOCK_EX);
        } catch (\Throwable) {
            // Journal indisponible : ne jamais bloquer la réponse.
        }
    }
}
