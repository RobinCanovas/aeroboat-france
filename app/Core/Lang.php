<?php
declare(strict_types=1);

namespace App\Core;

/** Traductions : app/lang/{fr,en}.php (tableaux imbriqués, clés pointées). Repli FR, puis la clé elle-même. */
final class Lang
{
    public const SUPPORTED = ['fr', 'en'];

    private static string $current = 'fr';
    /** @var array<string,array> */
    private static array $cache = [];

    public static function current(): string
    {
        return self::$current;
    }

    public static function set(string $lang): void
    {
        if (in_array($lang, self::SUPPORTED, true)) {
            self::$current = $lang;
        }
    }

    /** @return mixed chaîne, liste (tableau) ou null */
    public static function raw(string $key, ?string $lang = null)
    {
        $lang ??= self::$current;
        $node = self::load($lang);
        foreach (explode('.', $key) as $part) {
            if (!is_array($node) || !array_key_exists($part, $node)) {
                return $lang !== 'fr' ? self::raw($key, 'fr') : null;
            }
            $node = $node[$part];
        }
        return $node;
    }

    public static function has(string $key): bool
    {
        return self::raw($key) !== null;
    }

    public static function t(string $key, array $params = []): string
    {
        $v = self::raw($key);
        if (!is_string($v) && !is_int($v) && !is_float($v)) {
            Logger::debug('Clé de langue absente : ' . $key);
            return $key;
        }
        $s = (string) $v;
        if ($params !== []) {
            $map = [];
            foreach ($params as $k => $p) {
                $map[':' . ltrim((string) $k, ':')] = (string) $p;
            }
            $s = strtr($s, $map);
        }
        return $s;
    }

    /** Liste (ex. home.stats) : tableau, vide si absent. */
    public static function list(string $key): array
    {
        $v = self::raw($key);
        return is_array($v) ? $v : [];
    }

    private static function load(string $lang): array
    {
        if (!isset(self::$cache[$lang])) {
            $file = Config::root() . '/app/lang/' . $lang . '.php';
            $data = is_file($file) ? require $file : [];
            self::$cache[$lang] = is_array($data) ? $data : [];
        }
        return self::$cache[$lang];
    }
}
