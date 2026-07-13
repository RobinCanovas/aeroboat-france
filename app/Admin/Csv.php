<?php
declare(strict_types=1);

namespace App\Admin;

/** Export CSV : UTF-8 avec BOM, séparateur « ; », neutralisation des formules de tableur. */
final class Csv
{
    public const BOM = "\xEF\xBB\xBF";

    /** Préfixe d'une apostrophe toute cellule pouvant être lue comme une formule (= + - @, tabulation, CR). */
    public static function neutralize(string $value): string
    {
        if ($value !== '' && strpbrk($value[0], "=+-@\t\r") !== false) {
            return "'" . $value;
        }
        return $value;
    }

    /** @param list<string|int|float|null> $row */
    public static function row(array $row): string
    {
        $out = [];
        foreach ($row as $value) {
            $s = self::neutralize($value === null ? '' : (string) $value);
            if (strpbrk($s, ";\"\r\n") !== false) {
                $s = '"' . str_replace('"', '""', $s) . '"';
            }
            $out[] = $s;
        }
        return implode(';', $out);
    }

    /**
     * @param list<string> $header
     * @param list<list<string|int|float|null>> $rows
     */
    public static function build(array $header, array $rows): string
    {
        $lines = [self::row($header)];
        foreach ($rows as $r) {
            $lines[] = self::row($r);
        }
        return self::BOM . implode("\r\n", $lines) . "\r\n";
    }
}
