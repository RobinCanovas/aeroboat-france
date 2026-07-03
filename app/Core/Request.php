<?php
declare(strict_types=1);

namespace App\Core;

/** Requête courante. Méthodes statiques (appelables aussi en `$request->method()`). */
final class Request
{
    private static ?string $lang = null;

    public static function method(): string
    {
        $m = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        return $m === 'HEAD' ? 'GET' : $m;
    }

    public static function isPost(): bool
    {
        return self::method() === 'POST';
    }

    /** Chemin sans la base (« /test » retiré), décodé, toujours avec un slash initial. */
    public static function path(): string
    {
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $path = rawurldecode((string) (parse_url($uri, PHP_URL_PATH) ?? '/'));
        $base = Config::basePath();
        if ($base !== '' && ($path === $base || str_starts_with($path, $base . '/'))) {
            $path = substr($path, strlen($base));
        }
        $path = '/' . ltrim($path, '/');
        return str_contains($path, "\0") ? '/' : $path;
    }

    /** Valeur POST puis GET. */
    public static function input(string $k, $default = null)
    {
        if (array_key_exists($k, $_POST)) {
            return $_POST[$k];
        }
        return array_key_exists($k, $_GET) ? $_GET[$k] : $default;
    }

    /** Chaîne nettoyée (tableau → « »). */
    public static function str(string $k, string $default = ''): string
    {
        $v = self::input($k, $default);
        return is_string($v) ? trim($v) : $default;
    }

    public static function file(string $k): ?array
    {
        $f = $_FILES[$k] ?? null;
        if (!is_array($f) || !isset($f['error']) || is_array($f['error']) || (int) $f['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        return $f;
    }

    /** IP du client (REMOTE_ADDR uniquement : les en-têtes X-Forwarded-For sont falsifiables). */
    public static function ip(): string
    {
        return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    public static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443
            || str_starts_with((string) Config::get('app.url'), 'https://');
    }

    public static function lang(): string
    {
        return self::$lang ?? Lang::current();
    }

    public static function setLang(string $lang): void
    {
        self::$lang = $lang;
    }

    /** Langue préférée d'après Accept-Language (fr par défaut). */
    public static function preferredLang(): string
    {
        $header = strtolower((string) ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''));
        $best = 'fr';
        $bestQ = -1.0;
        foreach (explode(',', $header) as $i => $part) {
            $bits = explode(';', trim($part));
            $tag = substr(trim($bits[0]), 0, 2);
            $q = 1.0;
            foreach (array_slice($bits, 1) as $p) {
                if (preg_match('/^\s*q=([0-9.]+)/', $p, $m)) {
                    $q = (float) $m[1];
                }
            }
            $q -= $i * 0.0001;
            if (in_array($tag, Lang::SUPPORTED, true) && $q > $bestQ && $q > 0) {
                $best = $tag;
                $bestQ = $q;
            }
        }
        return $best;
    }
}
