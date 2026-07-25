<?php
declare(strict_types=1);

namespace App\Admin;

use App\Core\Upload;

/**
 * Suppression sûre des images téléversées (public/uploads/<sous-dossier>/<hex32>.webp).
 * Les valeurs du seed (noms de base du manifeste d'images, dans public/img/) ne correspondent jamais au motif et ne sont jamais supprimées.
 */
final class ImageFiles
{
    public const SUBDIRS = ['team', 'partners', 'posts'];

    /** Valeur stockée valide : « <sous-dossier>/<32 hex>.webp » avec un sous-dossier connu. */
    public static function validValue(?string $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }
        return preg_match('#^(' . implode('|', self::SUBDIRS) . ')/[0-9a-f]{32}\.webp$#', $value) === 1;
    }

    /** Chemin réel du fichier, ou null (valeur invalide, fichier absent, hors du dossier d'uploads). */
    public static function resolve(string $uploadsDir, ?string $value): ?string
    {
        if (!self::validValue($value)) {
            return null;
        }
        $base = realpath($uploadsDir);
        $path = realpath(rtrim($uploadsDir, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, (string) $value));
        if ($base === false || $path === false || !is_file($path)) {
            return null;
        }
        if (!str_starts_with($path, $base . DIRECTORY_SEPARATOR)) {
            return null;
        }
        return $path;
    }

    /** Supprime le fichier s'il existe. Retourne true si un fichier a été supprimé. */
    public static function delete(string $uploadsDir, ?string $value): bool
    {
        $path = self::resolve($uploadsDir, $value);
        return $path !== null && @unlink($path);
    }

    /** Dossier d'uploads du Core. */
    public static function dir(): string
    {
        return Upload::uploadsDir();
    }
}
