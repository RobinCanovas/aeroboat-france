<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Authentification admin (session). Rôles : admin (tout), editor (rédacteur).
 * Échecs de connexion :
 * - par couple (identifiant, IP hachée) : blocage après MAX_FAILS échecs en LOCK_WINDOW (délai croissant avant) ;
 * - par IP hachée, tous identifiants confondus : blocage après MAX_IP_FAILS (pulvérisation depuis une IP) ;
 * - par identifiant, toutes IP confondues : au-delà de SLOW_FAILS, ralentissement seulement (jamais de blocage,
 *   pour qu'un tiers ne puisse pas verrouiller un compte à distance).
 * Session : inactivité 2 h (Session), durée max MAX_AGE depuis la connexion, empreinte du hachage du mot de passe
 * (un changement de mot de passe invalide les autres sessions).
 */
final class Auth
{
    public const ROLES = ['admin', 'editor'];
    public const MAX_FAILS = 5;
    public const MAX_IP_FAILS = 20;
    public const SLOW_FAILS = 50;
    public const SLOW_DELAY_US = 3000000;
    public const LOCK_WINDOW = 900;
    public const MAX_AGE = 43200;

    private static ?array $user = null;
    private static bool $loaded = false;
    private static string $lastError = '';

    public static function hashPassword(string $password): string
    {
        $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
        return password_hash($password, $algo);
    }

    public static function fingerprint(string $passwordHash): string
    {
        return hash('sha256', $passwordHash);
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
        $at = (int) ($_SESSION['auth_at'] ?? 0);
        if ($at <= 0 || time() - $at > self::MAX_AGE) {
            self::forget();
            return null;
        }
        $row = Db::one('SELECT id, username, display_name, email, role, active, last_login_at, created_at, password_hash FROM {t:users} WHERE id = :id', ['id' => $id]);
        $fp = $_SESSION['auth_fp'] ?? '';
        if ($row === null || (int) $row['active'] !== 1 || !in_array($row['role'], self::ROLES, true)
            || !is_string($fp) || $fp === '' || !hash_equals(self::fingerprint((string) $row['password_hash']), $fp)) {
            self::forget();
            return null;
        }
        unset($row['password_hash']);
        return self::$user = $row;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    /** À appeler après un changement de mot de passe de l'utilisateur connecté : garde sa session valide. */
    public static function refreshFingerprint(): void
    {
        Session::start();
        $id = (int) ($_SESSION['auth_uid'] ?? 0);
        if ($id <= 0) {
            return;
        }
        $row = Db::one('SELECT password_hash FROM {t:users} WHERE id = :id', ['id' => $id]);
        if ($row !== null) {
            $_SESSION['auth_fp'] = self::fingerprint((string) $row['password_hash']);
        }
        self::$loaded = false;
        self::$user = null;
    }

    /** Dernière raison d'échec de attempt() : '' | 'invalid' | 'locked'. */
    public static function lastError(): string
    {
        return self::$lastError;
    }

    /** Blocage pour le couple (identifiant, IP courante) ou pour l'IP courante. */
    public static function isLocked(string $username): bool
    {
        return Throttle::count(self::pairBucket($username), self::LOCK_WINDOW) >= self::MAX_FAILS
            || Throttle::count('login_ip', self::LOCK_WINDOW) >= self::MAX_IP_FAILS;
    }

    /** Seuil global par identifiant (toutes IP) : ralentit, ne bloque pas. */
    public static function isSlowed(string $username): bool
    {
        return Throttle::count(self::userBucket($username), self::LOCK_WINDOW, 'any') >= self::SLOW_FAILS;
    }

    public static function attempt(string $username, string $password): bool
    {
        self::$lastError = '';
        $username = trim($username);
        if (self::isLocked($username)) {
            self::$lastError = 'locked';
            return false;
        }
        if (self::isSlowed($username) && PHP_SAPI !== 'cli') {
            usleep(self::SLOW_DELAY_US);
        }
        $row = $username === '' ? null : Db::one('SELECT * FROM {t:users} WHERE username = :u', ['u' => $username]);
        // Utilisateur inconnu : on vérifie quand même un hachage pour ne pas révéler l'existence du compte par le temps de réponse.
        $hash = $row['password_hash'] ?? password_hash(random_bytes(8), PASSWORD_BCRYPT);
        $ok = password_verify($password, (string) $hash) && $row !== null && (int) $row['active'] === 1;
        if (!$ok) {
            $fails = Throttle::count(self::pairBucket($username), self::LOCK_WINDOW);
            Throttle::hit(self::pairBucket($username), PHP_INT_MAX, self::LOCK_WINDOW);
            Throttle::hit('login_ip', PHP_INT_MAX, self::LOCK_WINDOW);
            Throttle::hit(self::userBucket($username), PHP_INT_MAX, self::LOCK_WINDOW, 'any');
            if (PHP_SAPI !== 'cli') {
                usleep(min(2000000, 250000 * (2 ** min($fails, 3))));
            }
            self::$lastError = 'invalid';
            Logger::warning('Échec de connexion admin');
            return false;
        }
        $hash = (string) $row['password_hash'];
        if (password_needs_rehash($hash, defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT)) {
            $hash = self::hashPassword($password);
            Db::update('users', (int) $row['id'], ['password_hash' => $hash]);
        }
        Throttle::clear(self::pairBucket($username));
        Session::regenerate();
        unset($_SESSION['_csrf']);
        $_SESSION['auth_uid'] = (int) $row['id'];
        $_SESSION['auth_at'] = time();
        $_SESSION['auth_fp'] = self::fingerprint($hash);
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

    /** Oublie l'authentification sans détruire la session (jeton CSRF et messages flash conservés). */
    private static function forget(): void
    {
        unset($_SESSION['auth_uid'], $_SESSION['auth_at'], $_SESSION['auth_fp']);
        self::$user = null;
    }

    private static function userBucket(string $username): string
    {
        return 'login_u:' . substr(hash('sha256', strtolower($username)), 0, 40);
    }

    private static function pairBucket(string $username): string
    {
        return 'login_p:' . substr(hash('sha256', strtolower($username)), 0, 40);
    }
}
