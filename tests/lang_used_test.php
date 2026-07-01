<?php
declare(strict_types=1);

/** Toute clé littérale t('a.b') / t_html('a.b') utilisée dans app/ existe dans fr.php (et donc en.php via la parité). */
return static function (): array {
    $frFile = ROOT . '/app/lang/fr.php';
    if (!is_file($frFile)) {
        return ['app/lang/fr.php doit exister'];
    }
    $keys = flatten_lang(require $frFile);
    // Une clé « préfixe » (ex. img.lake-alpine) existe si elle est une feuille ; un groupe n'est pas une clé utilisable.
    $errors = [];
    foreach (files_with_ext(ROOT . '/app', ['php']) as $file) {
        if (str_contains($file, DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR)) {
            continue;
        }
        $src = (string) file_get_contents($file);
        if (preg_match_all('/\bt(?:_html)?\(\s*[\'"]([a-z0-9_.\-]+)[\'"]/i', $src, $m)) {
            foreach (array_unique($m[1]) as $key) {
                if (!isset($keys[$key])) {
                    $errors[] = rel($file) . " : clé inconnue « $key »";
                }
            }
        }
        if (preg_match_all('/\bt_html\(\s*[\'"]([a-z0-9_.\-]+)[\'"]/i', $src, $m)) {
            foreach (array_unique($m[1]) as $key) {
                if (!str_ends_with($key, '_html')) {
                    $errors[] = rel($file) . " : t_html() réservé aux clés *_html (« $key »)";
                }
            }
        }
    }
    return $errors;
};
