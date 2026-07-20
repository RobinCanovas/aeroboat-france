<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Lang;
use App\Core\Logger;
use App\Core\View;

/**
 * Pages d'erreur localisées : gabarits errors/404|403|500 s'ils existent, sinon page minimale autonome.
 * Ne lève jamais d'exception (utilisé par le gestionnaire d'erreurs).
 */
final class ErrorController extends Controller
{
    public static function notFound(array $params = []): void
    {
        self::render(404);
    }

    public static function forbidden(array $params = []): void
    {
        self::render(403);
    }

    public static function serverError(?\Throwable $e = null): void
    {
        $detail = ($e !== null && Config::debug())
            ? get_class($e) . ': ' . $e->getMessage() . ' (' . str_replace([Config::root(), '\\'], ['', '/'], $e->getFile()) . ':' . $e->getLine() . ')'
            : null;
        self::render(500, $detail);
    }

    private static function render(int $code, ?string $detail = null): void
    {
        if (!headers_sent()) {
            http_response_code($code);
            header('Content-Type: text/html; charset=utf-8');
            header('Cache-Control: no-store');
        }
        $title = Lang::t("errors.$code.title");
        $html = null;
        if ($code !== 500 || $detail === null) {
            try {
                if (View::exists("errors/$code")) {
                    $meta = self::meta($title, Lang::t("errors.$code.text"));
                    $meta['robots'] = 'noindex, nofollow';
                    $html = View::render("errors/$code", ['meta' => $meta, 'page' => 'error', 'routeKey' => 'error', 'code' => $code]);
                }
            } catch (\Throwable $inner) {
                Logger::exception("Rendu de la page $code en échec", $inner);
                $html = null;
            }
        }
        if ($html === null) {
            $html = '<!doctype html><html lang="' . e(Lang::current()) . '"><head><meta charset="utf-8">'
                . '<meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex, nofollow">'
                . '<title>' . e($title) . '</title></head><body><main><h1>' . e($title) . '</h1><p>' . e(Lang::t("errors.$code.text")) . '</p>'
                . ($detail !== null ? '<pre>' . e($detail) . '</pre>' : '')
                . '<p><a href="' . e(url('/' . Lang::current() . '/')) . '">' . e(Lang::t('errors.404.cta')) . '</a></p></main></body></html>';
        }
        echo $html;
    }
}
