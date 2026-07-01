<?php
declare(strict_types=1);

/** Aplatit un tableau de langue en [clé.pointée => chaîne]. Listes : index numérique. */
function flatten_lang(array $arr, string $prefix = ''): array
{
    $out = [];
    foreach ($arr as $k => $v) {
        $key = $prefix === '' ? (string) $k : $prefix . '.' . $k;
        if (is_array($v)) {
            $out += flatten_lang($v, $key);
        } else {
            $out[$key] = (string) $v;
        }
    }
    return $out;
}

/** Liste récursive de fichiers par extension, en ignorant dossiers générés. */
function files_with_ext(string $dir, array $exts, array $skip = ['node_modules', 'vendor', 'dist', 'assets', '.git', 'uploads', 'storage']): array
{
    if (!is_dir($dir)) {
        return [];
    }
    $out = [];
    $it = new RecursiveIteratorIterator(new RecursiveCallbackFilterIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        static fn (SplFileInfo $f) => !($f->isDir() && in_array($f->getFilename(), $skip, true))
    ));
    foreach ($it as $f) {
        if ($f->isFile() && in_array(strtolower($f->getExtension()), $exts, true)) {
            $out[] = $f->getPathname();
        }
    }
    sort($out);
    return $out;
}

function rel(string $path): string
{
    return ltrim(str_replace([ROOT, '\\'], ['', '/'], $path), '/');
}
