<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Authentification admin (session). Rôles : admin (tout), editor (rédacteur).
 * Verrouillage progressif : échecs comptés par IP hachée et par identifiant, délai croissant puis blocage 15 min.
 */
final class Auth
{
    public const ROLES = ['admin', 'editor'];
    public const MAX_FAILS = 5;
    public const LOCK_WINDOW = 900;

    private static ?array $user = null;
    private static bool $loaded = false;
    private static string $lastError = '';

    public static function hashPassword(string $password): string
    {
        $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
        return password_hash($password, $algo);
    }

    public static function user(): ?array
    {
        if (self::$loaded) {
            return self::$user;
        }
        self::$loaded = true;
        Session::start();
        $id = (int) ($_SESSION['auth_uid'] ?? 0);
        if ($id <= 0) {
            return null;
        }
        $row = Db::one('SELECT id, username, display_name, email, role, active, last_login_at, created_at FROM {t:users} WHERE id = :id', ['id' => $id]);
        if ($row === null || (int) $row['active'] !== 1 || !in_array($row['role'], self::ROLES, true)) {
            unset($_SESSION['auth_uid']);
            return null;
        }
        return self::$user = $row;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    /** Dernière raison d'échec de attempt() : '' | 'invalid' | 'locked'. */
    public static function lastError(): string
    {
        return self::$lastError;
    }

    public static function isLocked(string $username): bool
    {
        return Throttle::count('login_ip', self::LOCK_WINDOW) >= self::MAX_FAILS * 2
            || Throttle::count(self::userBucket($username), self::LOCK_WINDOW, 'any') >= self::MAX_FAILS;
    }

    public static function attempt(string $username, string $password): bool
    {
        self::$lastError = '';
        $username = trim($username);
        if (self::isLocked($username)) {
            self::$lastError = 'locked';
            return false;
        }
        $row = $username === '' ? null : Db::one('SELECT * FROM {t:users} WHERE username = :u', ['u' => $username]);
        // Utilisateur inconnu : on vérifie quand même un hachage pour ne pas révéler l'existence du compte par le temps de réponse.
        $hash = $row['password_hash'] ?? password_hash(random_bytes(8), PASSWORD_BCRYPT);
        $ok = password_verify($password, (string) $hash) && $row !== null && (int) $row['active'] === 1;
        if (!$ok) {
            $fails = Throttle::count(self::userBucket($username), self::LOCK_WINDOW, 'any');
            Throttle::hit('login_ip', PHP_INT_MAX, self::LOCK_WINDOW);
            Throttle::hit(self::userBucket($username), PHP_INT_MAX, self::LOCK_WINDOW, 'any');
            if (PHP_SAPI !== 'cli') {
                usleep(min(2000000, 250000 * (2 ** min($fails, 3))));
            }
            self::$lastError = 'invalid';
            Logger::warning('Échec de connexion admin');
            return false;
        }
        if (password_needs_rehash((string) $row['password_hash'], defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT)) {
            Db::update('users', (int) $row['id'], ['password_hash' => self::hashPassword($password)]);
        }
        Throttle::clear(self::userBucket($username), 'any');
        Session::regenerate();
        $_SESSION['auth_uid'] = (int) $row['id'];
        $_SESSION['auth_at'] = time();
        Db::update('users', (int) $row['id'], ['last_login_at' => Db::now()]);
        self::$loaded = false;
        self::$user = null;
        return true;
    }

    public static function logout(): void
    {
        Session::destroy();
        self::$user = null;
        self::$loaded = true;
    }

    public static function hasRole(string $r): bool
    {
        $u = self::user();
        return $u !== null && ($u['role'] === $r || $u['role'] === 'admin');
    }

    /** Sans session : redirection vers /admin/login. Rôle insuffisant : 403. */
    public static function requireRole(string ...$roles): void
    {
        $u = self::user();
        if ($u === null) {
            Response::redirect(url('/admin/login'));
        }
        if ($roles !== [] && $u['role'] !== 'admin' && !in_array($u['role'], $roles, true)) {
            Response::forbidden();
        }
    }

    private static function userBucket(string $username): string
    {
        return 'login_u:' . substr(hash('sha256', strtolower($username)), 0, 40);
    }
}
