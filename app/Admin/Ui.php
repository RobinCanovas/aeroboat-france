<?php
declare(strict_types=1);

namespace App\Admin;

use App\Core\Config;
use App\Core\Csrf;

/** Rendu des gabarits admin (app/views/admin/*.php) dans la mise en page sobre de l'espace membres. */
final class Ui
{
    public const STATUS_APPLICATION = [
        'new' => 'Nouvelle',
        'reviewing' => 'En cours d\'examen',
        'interview' => 'Entretien',
        'accepted' => 'Acceptée',
        'declined' => 'Refusée',
    ];

    public static function root(): string
    {
        return dirname(__DIR__, 2);
    }

    /** URL (relative à l'hôte) d'une page admin : u('/articles') donne « /base/admin/articles ». */
    public static function u(string $path = ''): string
    {
        return rtrim((string) Config::basePath(), '/') . '/admin' . $path;
    }

    /** URL d'une ressource publique (ex. un envoi). */
    public static function pub(string $path): string
    {
        return rtrim((string) Config::basePath(), '/') . '/' . ltrim($path, '/');
    }

    public static function csrf(): string
    {
        return '<input type="hidden" name="_token" value="' . htmlspecialchars(Csrf::sessionToken(), ENT_QUOTES, 'UTF-8') . '">';
    }

    /** Rend un gabarit sans mise en page. */
    public static function render(string $template, array $data = []): string
    {
        return self::renderFile(self::root() . '/app/views/admin/' . $template . '.php', $data);
    }

    public static function renderFile(string $file, array $data = []): string
    {
        $render = static function (string $__file, array $__data): string {
            extract($__data, EXTR_SKIP);
            ob_start();
            try {
                include $__file;
            } catch (\Throwable $e) {
                ob_end_clean();
                throw $e;
            }
            return (string) ob_get_clean();
        };
        return $render($file, $data);
    }

    /** Page complète (menu, messages flash, pied) à partir d'un gabarit ou d'un contenu déjà rendu. */
    public static function page(Context $ctx, string $title, string $template, array $data = [], string $active = '', int $status = 200): Reply
    {
        return self::wrap($ctx, $title, self::render($template, $data), $active, $status);
    }

    public static function wrap(?Context $ctx, string $title, string $content, string $active = '', int $status = 200): Reply
    {
        $role = $ctx?->role();
        $menu = [];
        if ($ctx !== null && $role !== null) {
            $items = [
                ['dashboard', '', 'Tableau de bord', 'dashboard'],
                ['posts', '/articles', 'Articles', 'articles'],
                ['team', '/equipe', 'Équipe', 'equipe'],
                ['partners', '/partenaires', 'Partenaires', 'partenaires'],
                ['applications', '/candidatures', 'Candidatures', 'candidatures'],
                ['messages', '/messages', 'Messages', 'messages'],
                ['users', '/comptes', 'Comptes', 'comptes'],
                ['purge', '/rgpd', 'Purge RGPD', 'rgpd'],
            ];
            foreach ($items as [$area, $path, $label, $key]) {
                if (Policy::allowed($role, $area)) {
                    $menu[] = ['href' => self::u($path), 'label' => $label, 'key' => $key];
                }
            }
            foreach (ModuleLoader::discover(self::root() . '/app/Modules', $role) as $m) {
                $menu[] = ['href' => self::u('/modules/' . $m['key']), 'label' => $m['label'], 'key' => 'modules/' . $m['key'], 'module' => true];
            }
        }
        $html = self::render('layout', [
            'title' => $title,
            'content' => $content,
            'menu' => $menu,
            'active' => $active,
            'user' => $ctx?->user,
            'flashes' => Flash::take(),
            'isTest' => (bool) Config::isTest(),
        ]);
        return Reply::html($html, $status);
    }

    public static function error(?Context $ctx, int $status, string $message): Reply
    {
        $title = $status === 403 ? 'Accès refusé' : ($status === 404 ? 'Page introuvable' : 'Erreur');
        return self::wrap($ctx, $title, '<h1>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1><p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>', '', $status);
    }

    /** Valeur de cellule de liste, échappée et mise en forme selon le type du champ. */
    public static function cell(array $field, mixed $value): string
    {
        $type = (string) ($field['type'] ?? 'text');
        $v = $value === null ? '' : (string) $value;
        $html = match ($type) {
            'bool' => $v === '1' ? 'Oui' : 'Non',
            'select' => (string) (($field['options'][$v] ?? $v)),
            'datetime' => Time::utcToParis($v),
            default => mb_strlen($v, 'UTF-8') > 80 ? mb_substr($v, 0, 80, 'UTF-8') . '…' : $v,
        };
        if ($type === 'text' && $v === '') {
            return '';
        }
        return htmlspecialchars($html, ENT_QUOTES, 'UTF-8');
    }

    public static function statusLabel(string $status): string
    {
        return self::STATUS_APPLICATION[$status] ?? $status;
    }
}
