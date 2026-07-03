<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Joue les fichiers database/migrations/NNN_nom.sql non encore appliqués (idempotent).
 * Jetons : {{PREFIX}}, {{PK}}, {{OPTS}}. Une instruction par « ; » en fin de ligne. Appelable depuis le web.
 */
final class Migrator
{
    public static function dir(): string
    {
        return Config::root() . '/database/migrations';
    }

    /** @return string[] noms des migrations jouées lors de cet appel */
    public static function run(?string $dir = null): array
    {
        $dir ??= self::dir();
        self::ensureTable();
        $applied = array_column(self::appliedRows(), 'migration');
        $done = [];
        foreach (self::files($dir) as $file) {
            $name = basename($file);
            if (in_array($name, $applied, true)) {
                continue;
            }
            $pdo = Db::pdo();
            $sqlite = Db::driver() === 'sqlite';
            if ($sqlite) {
                $pdo->beginTransaction();
            }
            try {
                foreach (self::statements((string) file_get_contents($file)) as $stmt) {
                    $pdo->exec($stmt);
                }
                Db::insert('schema_migrations', ['migration' => $name, 'applied_at' => Db::now()]);
                if ($sqlite) {
                    $pdo->commit();
                }
            } catch (\Throwable $e) {
                if ($sqlite && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                throw new \RuntimeException("Migration $name en échec : " . $e->getMessage(), 0, $e);
            }
            $done[] = $name;
        }
        return $done;
    }

    /** @return array<int,array{migration:string,applied:bool,applied_at:?string}> */
    public static function status(?string $dir = null): array
    {
        self::ensureTable();
        $applied = [];
        foreach (self::appliedRows() as $r) {
            $applied[$r['migration']] = $r['applied_at'];
        }
        $out = [];
        foreach (self::files($dir ?? self::dir()) as $file) {
            $name = basename($file);
            $out[] = ['migration' => $name, 'applied' => isset($applied[$name]), 'applied_at' => $applied[$name] ?? null];
        }
        return $out;
    }

    /** Remplace les jetons selon le pilote courant. */
    public static function render(string $sql, ?string $driver = null): string
    {
        $driver ??= Db::driver();
        $mysql = $driver === 'mysql';
        return strtr($sql, [
            '{{PREFIX}}' => Db::prefix(),
            '{{PK}}' => $mysql ? 'INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT',
            '{{OPTS}}' => $mysql ? 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci' : '',
        ]);
    }

    /** @return string[] */
    public static function statements(string $sql, ?string $driver = null): array
    {
        $lines = [];
        foreach (preg_split('/\R/', $sql) ?: [] as $line) {
            if (str_starts_with(ltrim($line), '--')) {
                continue;
            }
            $lines[] = $line;
        }
        $out = [];
        foreach (preg_split('/;[ \t]*(?:\R|$)/', implode("\n", $lines)) ?: [] as $chunk) {
            $chunk = trim($chunk);
            if ($chunk !== '') {
                $out[] = self::render($chunk, $driver);
            }
        }
        return $out;
    }

    private static function ensureTable(): void
    {
        $sql = 'CREATE TABLE IF NOT EXISTS {{PREFIX}}schema_migrations (id {{PK}}, migration VARCHAR(190) NOT NULL, applied_at DATETIME NOT NULL) {{OPTS}}';
        Db::pdo()->exec(self::render($sql));
    }

    private static function appliedRows(): array
    {
        return Db::all('SELECT migration, applied_at FROM {t:schema_migrations} ORDER BY id');
    }

    /** @return string[] */
    private static function files(string $dir): array
    {
        $files = glob(rtrim($dir, '/\\') . '/[0-9][0-9][0-9]_*.sql') ?: [];
        sort($files, SORT_STRING);
        return $files;
    }
}
