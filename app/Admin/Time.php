<?php
declare(strict_types=1);

namespace App\Admin;

use DateTimeImmutable;
use DateTimeZone;

/** Dates : stockage UTC (Y-m-d H:i:s), saisie et affichage à l'heure de Paris. */
final class Time
{
    public const FMT_DB = 'Y-m-d H:i:s';

    /** « 2026-07-01T12:30 » (heure de Paris) vers « 2026-07-01 10:30:00 » (UTC) ; null si invalide. */
    public static function parisToUtc(string $local): ?string
    {
        $local = trim($local);
        if (preg_match('/^(\d{4}-\d{2}-\d{2})[T ](\d{2}:\d{2})(?::\d{2})?$/', $local, $m) !== 1) {
            return null;
        }
        $paris = new DateTimeZone('Europe/Paris');
        $dt = DateTimeImmutable::createFromFormat('!Y-m-d H:i', $m[1] . ' ' . $m[2], $paris);
        if ($dt === false || $dt->format('Y-m-d H:i') !== $m[1] . ' ' . $m[2]) {
            return null;
        }
        return $dt->setTimezone(new DateTimeZone('UTC'))->format(self::FMT_DB);
    }

    /** UTC (base) vers la valeur d'un champ datetime-local à l'heure de Paris. */
    public static function utcToParisInput(?string $utc): string
    {
        return self::utcToParis($utc, 'Y-m-d\TH:i');
    }

    public static function utcToParis(?string $utc, string $format = 'd/m/Y H:i'): string
    {
        if ($utc === null || trim($utc) === '') {
            return '';
        }
        try {
            $dt = new DateTimeImmutable($utc, new DateTimeZone('UTC'));
        } catch (\Exception) {
            return '';
        }
        return $dt->setTimezone(new DateTimeZone('Europe/Paris'))->format($format);
    }

    public static function nowUtc(): string
    {
        return gmdate(self::FMT_DB);
    }
}
