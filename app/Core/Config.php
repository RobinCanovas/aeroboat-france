<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Configuration : lit `.env` à la racine (ou le fichier désigné par la variable d'environnement ABF_ENV_FILE).
 * Format : CLE=valeur ; commentaire de fin de ligne = « #» précédé d'un espace, hors guillemets.
 */
final class Config
{
    /** @var array<string,string> */
    private static array $env = [];
    private static bool $loaded = false;

    public static function load(?string $file = null): void
    {
        self::$loaded = true;
        $file ??= (getenv('ABF_ENV_FILE') ?: null) ?? self::root() . '/.env';
        if (!is_file($file) || !is_readable($file)) {
            return;
        }
        self::$env = self::parse((string) file_get_contents($file)) + self::$env;
    }

    /** @return array<string,string> */
    public static function parse(string $content): array
    {
        $out = [];
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content) ?? $content;
        foreach (preg_split('/\R/', $content) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }
            if (str_starts_with($line, 'export ')) {
                $line = ltrim(substr($line, 7));
            }
            $pos = strpos($line, '=');
            if ($pos === false) {
                continue;
            }
            $key = trim(substr($line, 0, $pos));
            if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key)) {
                continue;
            }
            $raw = ltrim(substr($line, $pos + 1));
            if ($raw !== '' && ($raw[0] === '"' || $raw[0] === "'")) {
                $q = $raw[0];
                $end = strpos($raw, $q, 1);
                $value = $end === false ? substr($raw, 1) : substr($raw, 1, $end - 1);
                if ($q === '"') {
                    $value = str_replace(['\\n', '\\"', '\\\\'], ["\n", '"', '\\'], $value);
                }
            } else {
                $value = $raw;
                if (preg_match('/\s#/', $value, $m, PREG_OFFSET_CAPTURE)) {
                    $value = substr($value, 0, (int) $m[0][1]);
                }
                $value = rtrim($value);
            }
            $out[$key] = $value;
        }
        return $out;
    }

    public static function env(string $k, ?string $default = null): ?string
    {
        if (!self::$loaded) {
            self::load();
        }
        if (array_key_exists($k, self::$env)) {
            return self::$env[$k];
        }
        $v = getenv($k);
        return $v === false ? $default : $v;
    }

    /** Surcharge (tests, CLI de démonstration). */
    public static function set(string $k, ?string $v): void
    {
        if ($v === null) {
            unset(self::$env[$k]);
            return;
        }
        self::$env[$k] = $v;
    }

    /** Accès pointé : app.url, app.env, app.debug, app.key, db.prefix… (sinon APP_URL, DB_PREFIX…). */
    public static function get(string $key, ?string $default = null): ?string
    {
        if ($key === 'app.url') {
            return rtrim((string) self::env('APP_URL', 'http://127.0.0.1:8000'), '/');
        }
        return self::env(strtoupper(str_replace('.', '_', $key)), $default);
    }

    /** Chemin de APP_URL sans slash final : « /test » ou « ». */
    public static function basePath(): string
    {
        $path = (string) (parse_url((string) self::get('app.url'), PHP_URL_PATH) ?? '');
        return rtrim($path, '/');
    }

    /** Version de test : noindex, Disallow, bandeau. */
    public static function isTest(): bool
    {
        return self::env('SITE_NOINDEX', '1') === '1';
    }

    public static function debug(): bool
    {
        return self::env('APP_DEBUG', '0') === '1' && self::env('APP_ENV', 'production') !== 'production';
    }

    public static function root(): string
    {
        return defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2);
    }

    /** Clé HMAC de l'application (obligatoire, au moins 32 caractères). */
    public static function appKey(): string
    {
        $key = (string) self::env('APP_KEY', '');
        if (strlen($key) < 32) {
            throw new \RuntimeException('APP_KEY manquante ou trop courte (32 caractères minimum).');
        }
        return $key;
    }
}
