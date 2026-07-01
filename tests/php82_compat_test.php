<?php
declare(strict_types=1);

/**
 * Le PHP local peut être plus récent que celui d'OVH (8.2). Détecte les constructions 8.3+ les plus courantes.
 * Heuristique par jetons : ne remplace pas un test sur PHP 8.2 réel (la CI utilise 8.2).
 */
return static function (): array {
    $errors = [];
    $forbiddenFunctions = ['json_validate', 'mb_str_pad', 'mb_trim', 'mb_ltrim', 'mb_rtrim', 'array_find', 'array_find_key', 'array_any', 'array_all', 'str_increment', 'str_decrement'];
    foreach (files_with_ext(ROOT, ['php']) as $file) {
        if (str_starts_with(rel($file), 'tests/')) {
            continue;
        }
        $code = (string) file_get_contents($file);
        $tokens = token_get_all($code);
        $n = count($tokens);
        for ($i = 0; $i < $n; $i++) {
            $t = $tokens[$i];
            if (!is_array($t)) {
                continue;
            }
            [$id, $text, $line] = $t;
            $where = rel($file) . ':' . $line;
            if ($id === T_CONST) {
                // const NOM = ... (8.2) vs const type NOM = ... (8.3)
                $j = $i + 1;
                while ($j < $n && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) {
                    $j++;
                }
                $k = $j + 1;
                while ($k < $n && is_array($tokens[$k]) && $tokens[$k][0] === T_WHITESPACE) {
                    $k++;
                }
                if ($k < $n && is_array($tokens[$j]) && is_array($tokens[$k]) && in_array($tokens[$j][0], [T_STRING, T_NAME_QUALIFIED, T_ARRAY, T_STATIC], true) && $tokens[$k][0] === T_STRING) {
                    $errors[] = "$where : constante de classe typée (PHP 8.3)";
                }
            }
            if ($id === T_ATTRIBUTE) {
                $chunk = '';
                for ($j = $i; $j < min($n, $i + 6); $j++) {
                    $chunk .= is_array($tokens[$j]) ? $tokens[$j][1] : $tokens[$j];
                }
                if (stripos($chunk, 'Override') !== false) {
                    $errors[] = "$where : #[\\Override] (PHP 8.3)";
                }
            }
            if ($id === T_STRING && in_array(strtolower($text), $forbiddenFunctions, true)) {
                $prev = $tokens[$i - 1] ?? null;
                $isCall = !is_array($prev) || !in_array($prev[0], [T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION], true);
                $next = $tokens[$i + 1] ?? null;
                if ($isCall && $next === '(') {
                    $errors[] = "$where : fonction $text() absente de PHP 8.2";
                }
            }
        }
        if (preg_match('/\b(public|protected|private)\s*\(\s*set\s*\)/', $code, $m, PREG_OFFSET_CAPTURE)) {
            $errors[] = rel($file) . ' : visibilité asymétrique (PHP 8.4)';
        }
        if (preg_match('/\b(?:public|protected|private)?\s*(?:readonly\s+)?(?:\??[A-Za-z_\\\\]+\s+)\$\w+\s*\{\s*(?:get|set)\b/', $code)) {
            $errors[] = rel($file) . ' : hook de propriété (PHP 8.4)';
        }
        if (preg_match('/::\{\s*\$/', $code)) {
            $errors[] = rel($file) . ' : accès dynamique à une constante de classe ::{$x} (PHP 8.3)';
        }
    }
    return $errors;
};
