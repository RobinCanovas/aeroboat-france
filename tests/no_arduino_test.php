<?php
declare(strict_types=1);

/** Aucune occurrence d'« Arduino » dans le code, le contenu, la doc livrée (hors ce test et le rapport de contenu). */
return static function (): array {
    $errors = [];
    $self = realpath(__FILE__);
    $exts = ['php', 'js', 'mjs', 'css', 'json', 'md', 'html', 'sql', 'yml', 'yaml', 'txt', 'py'];
    // docs/ (sources du site actuel, consignes) cite le mot pour l'interdire : hors périmètre.
    $skipFiles = ['package-lock.json'];
    $skipDirs = ['node_modules', 'vendor', 'dist', 'assets', '.git', 'uploads', 'storage', 'docs'];
    foreach (files_with_ext(ROOT, $exts, $skipDirs) as $file) {
        if (realpath($file) === $self || in_array(basename($file), $skipFiles, true)) {
            continue;
        }
        if (stripos((string) file_get_contents($file), 'arduino') !== false) {
            $errors[] = rel($file) . ' contient « Arduino »';
        }
    }
    return $errors;
};
