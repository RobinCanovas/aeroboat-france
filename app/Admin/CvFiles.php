<?php
declare(strict_types=1);

namespace App\Admin;

/** Accès sûr aux CV stockés dans storage/cv/ (hors racine web). Pur : le dossier est passé en paramètre. */
final class CvFiles
{
    /** Nom accepté : caractères simples, sans séparateur ni « .. », 100 caractères au plus. */
    public static function validName(string $name): bool
    {
        return preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]{0,99}$/', $name) === 1 && !str_contains($name, '..');
    }

    /** Chemin réel du CV, ou null si le nom est invalide, le fichier absent ou hors du dossier. */
    public static function resolve(string $dir, string $name): ?string
    {
        if (!self::validName($name)) {
            return null;
        }
        $base = realpath($dir);
        $path = realpath(rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $name);
        if ($base === false || $path === false || !is_file($path)) {
            return null;
        }
        if (!str_starts_with($path, $base . DIRECTORY_SEPARATOR)) {
            return null;
        }
        return $path;
    }

    /** Supprime le fichier s'il existe. Retourne true si un fichier a été supprimé. */
    public static function delete(string $dir, ?string $name): bool
    {
        if ($name === null || $name === '') {
            return false;
        }
        $path = self::resolve($dir, $name);
        return $path !== null && @unlink($path);
    }
}
