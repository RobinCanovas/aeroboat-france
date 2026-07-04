<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Réponses : chaque méthode envoie la réponse puis termine le script (pas besoin de `return`).
 * Les en-têtes de sécurité sont posés en amont par le Kernel.
 */
final class Response
{
    public static function redirect(string $url, int $code = 302): void
    {
        $url = str_replace(["\r", "\n", "\0"], '', $url);
        if (str_starts_with($url, '//') || str_starts_with($url, '/\\')) {
            $url = url('/');
        } elseif (!preg_match('#^https?://#i', $url)) {
            $url = url($url);
        }
        http_response_code($code);
        header('Location: ' . $url);
        header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><meta charset="utf-8"><a href="' . e($url) . '">' . e($url) . '</a>';
        self::finish();
    }

    public static function html(string $s, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: text/html; charset=utf-8');
        echo $s;
        self::finish();
    }

    public static function text(string $s, int $code = 200, string $type = 'text/plain'): void
    {
        http_response_code($code);
        header('Content-Type: ' . $type . '; charset=utf-8');
        echo $s;
        self::finish();
    }

    public static function json($data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        self::finish();
    }

    public static function download(string $path, string $name, string $mime): void
    {
        if (!is_file($path) || !is_readable($path)) {
            self::notFound();
        }
        $safe = preg_replace('/[^A-Za-z0-9._-]+/', '_', $name) ?: 'fichier';
        http_response_code(200);
        header('Content-Type: ' . str_replace(["\r", "\n"], '', $mime));
        header('Content-Length: ' . (string) filesize($path));
        header('Content-Disposition: attachment; filename="' . $safe . '"; filename*=UTF-8\'\'' . rawurlencode($name));
        header('Cache-Control: private, no-store');
        readfile($path);
        self::finish();
    }

    public static function notFound(): void
    {
        \App\Controllers\ErrorController::notFound();
        self::finish();
    }

    public static function forbidden(): void
    {
        \App\Controllers\ErrorController::forbidden();
        self::finish();
    }

    private static function finish(): void
    {
        exit;
    }
}
