<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Jetons anti-CSRF.
 * - Formulaires publics, sans cookie ni session : « ts.hmac(APP_KEY, form|ts) » + délai minimum (anti-robot) + âge maximum.
 * - Admin : jeton aléatoire stocké en session.
 */
final class Csrf
{
    public const OK = 'ok';
    public const INVALID = 'invalid';
    public const EXPIRED = 'expired';
    public const TOO_FAST = 'too_fast';

    public static function formToken(string $form, ?int $now = null): string
    {
        $ts = (string) ($now ?? time());
        return $ts . '.' . hash_hmac('sha256', $form . '|' . $ts, Config::appKey());
    }

    public static function verifyForm(string $form, string $token, int $minDelay = 3, int $maxAge = 3600): bool
    {
        return self::checkForm($form, $token, $minDelay, $maxAge) === self::OK;
    }

    /** Diagnostic détaillé : ok | invalid | expired | too_fast. */
    public static function checkForm(string $form, string $token, int $minDelay = 3, int $maxAge = 3600, ?int $now = null): string
    {
        if (!preg_match('/^(\d{1,12})\.([a-f0-9]{64})$/', $token, $m)) {
            return self::INVALID;
        }
        $expected = hash_hmac('sha256', $form . '|' . $m[1], Config::appKey());
        if (!hash_equals($expected, $m[2])) {
            return self::INVALID;
        }
        $age = ($now ?? time()) - (int) $m[1];
        if ($age < -5) {
            return self::INVALID;
        }
        if ($age > $maxAge) {
            return self::EXPIRED;
        }
        if ($age < $minDelay) {
            return self::TOO_FAST;
        }
        return self::OK;
    }

    public static function sessionToken(): string
    {
        Session::start();
        if (empty($_SESSION['_csrf']) || !is_string($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function verifySession(string $t): bool
    {
        Session::start();
        $expected = $_SESSION['_csrf'] ?? '';
        return is_string($expected) && $expected !== '' && $t !== '' && hash_equals($expected, $t);
    }
}
