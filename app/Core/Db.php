<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOStatement;

/**
 * Accès PDO (SQLite local, MySQL OVH). Requêtes préparées uniquement.
 * Dans le SQL, `{t:users}` est remplacé par le nom préfixé (`abf_users`).
 */
final class Db
{
    private static ?PDO $pdo = null;
    private static ?string $prefix = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            self::$pdo = self::connect();
        }
        return self::$pdo;
    }

    /** Remplace la connexion (tests, démonstration). */
    public static function swap(?PDO $pdo, ?string $prefix = null): void
    {
        self::$pdo = $pdo;
        self::$prefix = $prefix === null ? null : self::checkPrefix($prefix);
    }

    public static function connect(): PDO
    {
        $driver = (string) Config::env('DB_DRIVER', 'sqlite');
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        if ($driver === 'mysql') {
            $dsn = 'mysql:host=' . Config::env('DB_HOST', 'localhost')
                . (Config::env('DB_PORT') ? ';port=' . (int) Config::env('DB_PORT') : '')
                . ';dbname=' . Config::env('DB_NAME', '') . ';charset=utf8mb4';
            $pdo = new PDO($dsn, (string) Config::env('DB_USER', ''), (string) Config::env('DB_PASS', ''), $options);
            $pdo->exec("SET time_zone = '+00:00'");
            return $pdo;
        }
        if ($driver !== 'sqlite') {
            throw new \RuntimeException('DB_DRIVER inconnu (sqlite|mysql).');
        }
        $path = (string) Config::env('DB_PATH', 'database/dev.sqlite');
        if (!preg_match('#^([A-Za-z]:[\\\\/]|/)#', $path) && $path !== ':memory:') {
            $path = Config::root() . '/' . $path;
        }
        return self::sqlite($path);
    }

    public static function sqlite(string $path): PDO
    {
        if ($path !== ':memory:' && !is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }
        $pdo = new PDO('sqlite:' . $path, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA busy_timeout = 5000');
        return $pdo;
    }

    public static function driver(): string
    {
        return (string) self::pdo()->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    public static function prefix(): string
    {
        if (self::$prefix === null) {
            self::$prefix = self::checkPrefix((string) Config::env('DB_PREFIX', 'abf_'));
        }
        return self::$prefix;
    }

    public static function checkPrefix(string $prefix): string
    {
        if (stripos($prefix, 'mod759') === 0) {
            throw new \RuntimeException('Préfixe interdit : les tables mod759_ appartiennent à la production WordPress.');
        }
        if (!preg_match('/^[A-Za-z0-9_]{0,20}$/', $prefix)) {
            throw new \RuntimeException('DB_PREFIX invalide.');
        }
        return $prefix;
    }

    public static function table(string $name): string
    {
        if (!preg_match('/^[a-z0-9_]+$/', $name)) {
            throw new \InvalidArgumentException('Nom de table invalide.');
        }
        return self::prefix() . $name;
    }

    public static function sql(string $sql): string
    {
        return (string) preg_replace_callback('/\{t:([a-z0-9_]+)\}/', static fn (array $m) => self::table($m[1]), $sql);
    }

    public static function run(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::pdo()->prepare(self::sql($sql));
        foreach ($params as $k => $v) {
            $name = is_int($k) ? $k + 1 : (str_starts_with((string) $k, ':') ? (string) $k : ':' . $k);
            $type = is_int($v) ? PDO::PARAM_INT : (is_bool($v) ? PDO::PARAM_BOOL : ($v === null ? PDO::PARAM_NULL : PDO::PARAM_STR));
            $stmt->bindValue($name, $v, $type);
        }
        $stmt->execute();
        return $stmt;
    }

    public static function all(string $sql, array $params = []): array
    {
        return self::run($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function one(string $sql, array $params = []): ?array
    {
        $row = self::run($sql, $params)->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    public static function insert(string $table, array $data): int
    {
        $cols = array_keys($data);
        array_walk($cols, [self::class, 'checkColumn']);
        $sql = 'INSERT INTO ' . self::table($table) . ' (' . implode(', ', array_map(static fn ($c) => "`$c`", $cols)) . ')'
            . ' VALUES (' . implode(', ', array_map(static fn ($c) => ':' . $c, $cols)) . ')';
        self::run($sql, $data);
        return (int) self::pdo()->lastInsertId();
    }

    public static function update(string $table, int $id, array $data): void
    {
        if ($data === []) {
            return;
        }
        $sets = [];
        foreach (array_keys($data) as $c) {
            self::checkColumn($c);
            $sets[] = "`$c` = :$c";
        }
        $data['__id'] = $id;
        self::run('UPDATE ' . self::table($table) . ' SET ' . implode(', ', $sets) . ' WHERE id = :__id', $data);
    }

    public static function delete(string $table, int $id): void
    {
        self::run('DELETE FROM ' . self::table($table) . ' WHERE id = :id', ['id' => $id]);
    }

    /** Date UTC au format SQL. */
    public static function now(?int $ts = null): string
    {
        return gmdate('Y-m-d H:i:s', $ts ?? time());
    }

    private static function checkColumn(string $c): void
    {
        if (!preg_match('/^[a-z_][a-z0-9_]*$/', $c)) {
            throw new \InvalidArgumentException('Nom de colonne invalide.');
        }
    }
}
