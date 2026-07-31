<?php
declare(strict_types=1);

namespace App\Core;

use DateTimeImmutable;
use DateTimeZone;

/**
 * Durées de conservation RGPD : messages 12 mois, candidatures 24 mois (CV compris), limite de débit 24 h,
 * journaux storage/logs/*.log 90 jours.
 * purge() supprime et retourne les décomptes ; runDaily() l'exécute au plus une fois par 24 h, sans jamais bloquer.
 */
final class Retention
{
    public const MESSAGES_MONTHS = 12;
    public const APPLICATIONS_MONTHS = 24;
    public const INTERVAL = 86400;
    public const LOGS_DAYS = 90;

    /** @return array{messages:string,applications:string} seuils UTC « Y-m-d H:i:s » */
    public static function cutoffs(?DateTimeImmutable $now = null): array
    {
        $now ??= new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $now = $now->setTimezone(new DateTimeZone('UTC'));
        return [
            'messages' => $now->modify('-' . self::MESSAGES_MONTHS . ' months')->format('Y-m-d H:i:s'),
            'applications' => $now->modify('-' . self::APPLICATIONS_MONTHS . ' months')->format('Y-m-d H:i:s'),
        ];
    }

    /** @return array{messages:int,applications:int,cv:int,throttle:int,logs:int} */
    public static function purge(?string $cvDir = null, ?string $logDir = null): array
    {
        $cut = self::cutoffs();
        $cv = 0;
        $apps = Db::all('SELECT id, cv_file FROM {t:applications} WHERE created_at < :c', ['c' => $cut['applications']]);
        foreach ($apps as $a) {
            if (Upload::deletePdf(isset($a['cv_file']) ? (string) $a['cv_file'] : null, $cvDir)) {
                $cv++;
            }
        }
        $applications = Db::run('DELETE FROM {t:applications} WHERE created_at < :c', ['c' => $cut['applications']])->rowCount();
        $messages = Db::run('DELETE FROM {t:messages} WHERE created_at < :c', ['c' => $cut['messages']])->rowCount();
        $throttle = Throttle::purge();
        $logs = self::purgeLogs($logDir);
        return ['messages' => $messages, 'applications' => $applications, 'cv' => $cv, 'throttle' => $throttle, 'logs' => $logs];
    }

    /** Supprime les fichiers *.log de storage/logs/ modifiés il y a plus de 90 jours (jamais le repère, jamais ailleurs). */
    public static function purgeLogs(?string $logDir = null): int
    {
        $base = realpath($logDir ?? Config::root() . '/storage/logs');
        if ($base === false) {
            return 0;
        }
        $limit = time() - self::LOGS_DAYS * 86400;
        $n = 0;
        foreach (glob($base . DIRECTORY_SEPARATOR . '*.log') ?: [] as $f) {
            $real = realpath($f);
            if ($real === false || dirname($real) !== $base || !is_file($real) || !str_ends_with($real, '.log')) {
                continue;
            }
            $mtime = @filemtime($real);
            if ($mtime !== false && $mtime < $limit && @unlink($real)) {
                $n++;
            }
        }
        return $n;
    }

    public static function markerFile(): string
    {
        return Config::root() . '/storage/logs/.last-retention';
    }

    /**
     * Purge au plus une fois par 24 h (repère storage/logs/.last-retention). Ne lève jamais d'exception.
     * @return array|null décomptes si la purge a tourné, null sinon
     */
    public static function runDaily(?string $marker = null): ?array
    {
        try {
            $marker ??= self::markerFile();
            $last = is_file($marker) ? (int) trim((string) @file_get_contents($marker)) : 0;
            if ($last > 0 && time() - $last < self::INTERVAL) {
                return null;
            }
            $dir = dirname($marker);
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            // Repère écrit avant la purge : deux requêtes simultanées ne purgent pas deux fois.
            @file_put_contents($marker, (string) time(), LOCK_EX);
            $counts = self::purge();
            Logger::info('Purge de conservation', $counts);
            return $counts;
        } catch (\Throwable $e) {
            Logger::exception('Purge de conservation en échec', $e);
            return null;
        }
    }
}
