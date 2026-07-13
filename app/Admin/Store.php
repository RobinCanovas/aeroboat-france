<?php
declare(strict_types=1);

namespace App\Admin;

use App\Core\Db;

/** Accès base de l'admin : requêtes préparées via Core\Db, noms de tables écrits `{t:nom}`. */
final class Store
{
    /** @return list<array<string,mixed>> */
    public static function all(string $sql, array $params = []): array
    {
        return Db::all($sql, $params);
    }

    /** @return array<string,mixed>|null */
    public static function one(string $sql, array $params = []): ?array
    {
        return Db::one($sql, $params);
    }

    public static function run(string $sql, array $params = []): void
    {
        Db::run($sql, $params);
    }

    public static function count(string $sql, array $params = []): int
    {
        $row = Db::one($sql, $params);
        return $row === null ? 0 : (int) array_values($row)[0];
    }

    /** @param array<string,mixed> $data */
    public static function insert(string $table, array $data): int
    {
        self::assertName($table);
        $cols = array_keys($data);
        foreach ($cols as $c) {
            self::assertName((string) $c);
        }
        $sql = 'INSERT INTO {t:' . $table . '} (' . implode(', ', $cols) . ') VALUES (' . implode(', ', array_fill(0, count($cols), '?')) . ')';
        Db::run($sql, array_values($data));
        return (int) Db::pdo()->lastInsertId();
    }

    /** @param array<string,mixed> $data */
    public static function update(string $table, int $id, array $data): void
    {
        self::assertName($table);
        $sets = [];
        foreach (array_keys($data) as $c) {
            self::assertName((string) $c);
            $sets[] = $c . ' = ?';
        }
        Db::run('UPDATE {t:' . $table . '} SET ' . implode(', ', $sets) . ' WHERE id = ?', [...array_values($data), $id]);
    }

    public static function delete(string $table, int $id): void
    {
        self::assertName($table);
        Db::run('DELETE FROM {t:' . $table . '} WHERE id = ?', [$id]);
    }

    /** Échappe % _ ! pour LIKE ... ESCAPE '!' (portable SQLite / MySQL). */
    public static function like(string $term): string
    {
        return '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term) . '%';
    }

    private static function assertName(string $n): void
    {
        if (preg_match('/^[a-z][a-z0-9_]*$/', $n) !== 1) {
            throw new \InvalidArgumentException('Nom SQL invalide.');
        }
    }
}
