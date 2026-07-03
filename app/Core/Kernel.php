<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Point d'entrée web : en-têtes de sécurité, redirection de langue, admin, SEO, routes localisées, 404.
 * Aucune session ni cookie côté public : seul App\Admin\Router (D) démarre une session.
 */
final class Kernel
{
    public static function handle(): void
    {
        Security::sendHeaders();
        $path = Request::path();
        $method = Request::method();

        if ($path === '/' || $path === '') {
            $lang = Request::preferredLang();
            header('Vary: Accept-Language');
            Response::redirect(Config::get('app.url') . '/' . $lang . '/', 302);
        }

        if ($path === '/sitemap.xml') {
            \App\Controllers\SeoController::sitemap();
            return;
        }
        if ($path === '/robots.txt') {
            \App\Controllers\SeoController::robots();
            return;
        }

        if ($path === '/admin' || str_starts_with($path, '/admin/')) {
            Lang::set('fr');
            Request::setLang('fr');
            $class = 'App\\Admin\\Router';
            if (!class_exists($class) || !method_exists($class, 'dispatch')) {
                \App\Controllers\ErrorController::notFound();
                return;
            }
            $out = $class::dispatch();
            if (is_string($out)) {
                Response::html($out);
            }
            return;
        }

        if (preg_match('#^/(fr|en)(/.*)?$#', $path, $m)) {
            Lang::set($m[1]);
            Request::setLang($m[1]);
            $rest = $m[2] ?? '';
            if ($rest === '') {
                Response::redirect(Config::get('app.url') . '/' . $m[1] . '/', 301);
            }
            if ($rest !== '/' && str_ends_with($rest, '/') && $method === 'GET') {
                Response::redirect(Config::get('app.url') . rtrim($path, '/') . self::query(), 301);
            }
        } else {
            $lang = Request::preferredLang();
            Lang::set($lang);
            Request::setLang($lang);
            \App\Controllers\ErrorController::notFound();
            return;
        }

        $route = Router::match($path);
        if ($route === null) {
            \App\Controllers\ErrorController::notFound();
            return;
        }
        if (!in_array($method, $route['methods'], true)) {
            header('Allow: ' . implode(', ', $route['methods']));
            Response::text('Method Not Allowed', 405);
        }
        Router::setCurrent($route['key'], $route['params']);

        [$controller, $action] = explode('@', $route['action'], 2);
        $class = 'App\\Controllers\\' . $controller;
        if (!class_exists($class) || !method_exists($class, $action)) {
            throw new \RuntimeException('Action introuvable : ' . $route['action']);
        }
        $class::$action($route['params']);
    }

    private static function query(): string
    {
        $q = (string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_QUERY) ?? '');
        return $q === '' ? '' : '?' . $q;
    }
}
