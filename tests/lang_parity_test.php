<?php
declare(strict_types=1);

/** Parité totale des clés FR/EN, mêmes paramètres :nom, pas de chaîne vide, HTML seulement dans *_html. */
return static function (): array {
    $errors = [];
    $frFile = ROOT . '/app/lang/fr.php';
    $enFile = ROOT . '/app/lang/en.php';
    if (!is_file($frFile) || !is_file($enFile)) {
        return ['app/lang/fr.php et en.php doivent exister'];
    }
    $fr = flatten_lang(require $frFile);
    $en = flatten_lang(require $enFile);

    foreach (array_diff_key($fr, $en) as $k => $_) {
        $errors[] = "clé absente de en.php : $k";
    }
    foreach (array_diff_key($en, $fr) as $k => $_) {
        $errors[] = "clé absente de fr.php : $k";
    }
    foreach (array_intersect_key($fr, $en) as $k => $frText) {
        $enText = $en[$k];
        preg_match_all('/:[a-z_]+\b/', $frText, $a);
        preg_match_all('/:[a-z_]+\b/', $enText, $b);
        sort($a[0]);
        sort($b[0]);
        if ($a[0] !== $b[0]) {
            $errors[] = "paramètres différents pour $k";
        }
        if (trim($frText) === '' || trim($enText) === '') {
            $errors[] = "valeur vide pour $k";
        }
        $isHtmlKey = (bool) preg_match('/_html$/', $k);
        foreach ([$frText, $enText] as $text) {
            if (!$isHtmlKey && preg_match('/<[a-z][^>]*>/i', $text)) {
                $errors[] = "HTML dans une clé non *_html : $k";
                break;
            }
            if ($isHtmlKey && preg_match('/<(?!\/?(strong|em|a|br)\b)[a-z]/i', $text)) {
                $errors[] = "balise non autorisée dans $k";
                break;
            }
        }
    }
    return $errors;
};
