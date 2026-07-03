<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Session : uniquement pour l'admin. Cookie « abf_admin » (HttpOnly, SameSite=Strict, Secure si https),
 * restreint au chemin {basePath}/admin, fichiers dans storage/sessions/.
 */
final class Session
{
    public const NAME = 'abf_admin';
    public const IDLE = 7200;

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        if (PHP_SAPI === 'cli') {
            $_SESSION ??= [];
            return;
        }
        $dir = Config::root() . '/storage/sessions';
        if (!is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.gc_maxlifetime', (string) self::IDLE);
        session_save_path($dir);
        session_name(self::NAME);
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => self::cookiePath(),
            'domain' => '',
            'secure' => Request::isHttps(),
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_cache_limiter('nocache');
        @session_start();

        $now = time();
        $last = (int) ($_SESSION['_last'] ?? $now);
        if ($now - $last > self::IDLE) {
            $_SESSION = [];
            session_regenerate_id(true);
        }
        $_SESSION['_last'] = $now;
    }

    public static function cookiePath(): string
    {
        return Config::basePath() . '/admin';
    }

    public static function regenerate(): void
    {
        self::start();
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function get(string $k, $default = null)
    {
        self::start();
        return $_SESSION[$k] ?? $default;
    }

    public static function set(string $k, $v): void
    {
        self::start();
        $_SESSION[$k] = $v;
    }

    /** Message flash : écrit (2 arguments) ou lit et efface (1 argument). */
    public static function flash(string $k, $v = null)
    {
        self::start();
        if ($v !== null) {
            $_SESSION['_flash'][$k] = $v;
            return null;
        }
        $out = $_SESSION['_flash'][$k] ?? null;
        unset($_SESSION['_flash'][$k]);
        return $out;
    }

    public static function destroy(): void
    {
        self::start();
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            if (!headers_sent()) {
                setcookie(self::NAME, '', [
                    'expires' => time() - 3600,
                    'path' => self::cookiePath(),
                    'secure' => Request::isHttps(),
                    'httponly' => true,
                    'samesite' => 'Strict',
                ]);
            }
            session_destroy();
        }
    }
}
