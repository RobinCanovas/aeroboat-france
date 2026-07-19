<?php
declare(strict_types=1);

namespace App\Core;

/** Limite de débit en base. L'IP n'est jamais stockée en clair : HMAC-SHA256 avec APP_KEY. */
final class Throttle
{
    public const RETENTION = 86400;

    public static function ipHash(?string $ip = null): string
    {
        return hash_hmac('sha256', $ip ?? Request::ip(), Config::appKey());
    }

    /** Enregistre un passage ; false = limite atteinte (le passage n'est alors pas compté). */
    public static function hit(string $bucket, int $limit, int $windowSec, ?string $ip = null): bool
    {
        $bucket = substr($bucket, 0, 60);
        $hash = self::ipHash($ip);
        $now = time();
        // Table minuscule : purge des entrées expirées à chaque passage.
        self::purge();
        if (self::count($bucket, $windowSec, $ip) >= $limit) {
            return false;
        }
        Db::insert('throttle', ['bucket' => $bucket, 'ip_hash' => $hash, 'created_at' => $now]);
        return true;
    }

    public static function count(string $bucket, int $windowSec, ?string $ip = null): int
    {
        $row = Db::one(
            'SELECT COUNT(*) AS n FROM {t:throttle} WHERE bucket = :b AND ip_hash = :h AND created_at > :since',
            ['b' => substr($bucket, 0, 60), 'h' => self::ipHash($ip), 'since' => time() - $windowSec]
        );
        return (int) ($row['n'] ?? 0);
    }

    public static function clear(string $bucket, ?string $ip = null): void
    {
        Db::run('DELETE FROM {t:throttle} WHERE bucket = :b AND ip_hash = :h', ['b' => substr($bucket, 0, 60), 'h' => self::ipHash($ip)]);
    }

    /** Supprime les entrées plus anciennes que la rétention (24 h). Retourne le nombre de lignes supprimées. */
    public static function purge(int $olderThan = self::RETENTION): int
    {
        return Db::run('DELETE FROM {t:throttle} WHERE created_at < :t', ['t' => time() - $olderThan])->rowCount();
    }
}
