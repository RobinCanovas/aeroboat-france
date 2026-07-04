<?php
declare(strict_types=1);

namespace App\Core;

/** En-têtes de sécurité (CONTRACTS §8) et nonce CSP par requête. */
final class Security
{
    private static ?string $nonce = null;

    public static function nonce(): string
    {
        if (self::$nonce === null) {
            self::$nonce = rtrim(strtr(base64_encode(random_bytes(18)), '+/', '-_'), '=');
        }
        return self::$nonce;
    }

    public static function viteOrigin(): string
    {
        return rtrim((string) Config::env('VITE_ORIGIN', 'http://localhost:5173'), '/');
    }

    public static function csp(): string
    {
        $vite = Config::env('VITE_DEV', '0') === '1' ? self::viteOrigin() : '';
        $ws = $vite !== '' ? ' ' . preg_replace('#^http#', 'ws', $vite) : '';
        $n = "'nonce-" . self::nonce() . "'";
        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' $n" . ($vite !== '' ? " $vite" : ''),
            "style-src 'self'" . ($vite !== '' ? " $vite" : ''),
            "img-src 'self' data:",
            "font-src 'self'" . ($vite !== '' ? " $vite" : ''),
            "connect-src 'self'" . ($vite !== '' ? " $vite$ws" : ''),
            "object-src 'none'",
            "base-uri 'none'",
            "form-action 'self'",
            "frame-ancestors 'none'",
        ]);
    }

    public static function sendHeaders(): void
    {
        if (headers_sent()) {
            return;
        }
        header_remove('X-Powered-By');
        header('Content-Security-Policy: ' . self::csp());
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()');
        header('X-Frame-Options: DENY');
        header('Cross-Origin-Opener-Policy: same-origin');
        if (Request::isHttps()) {
            header('Strict-Transport-Security: max-age=31536000');
        }
        if (Config::isTest()) {
            header('X-Robots-Tag: noindex, nofollow');
        }
    }
}
