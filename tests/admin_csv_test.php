<?php
declare(strict_types=1);

/** Export CSV : BOM UTF-8, séparateur « ; », guillemets, neutralisation des formules. */
return static function (): array {
    require_once ROOT . '/app/Admin/Csv.php';
    $C = \App\Admin\Csv::class;
    $errors = [];

    foreach (['=1+1', '+33612345678', '-2+3', '@SUM(A1)', "\t=1", "\r=1"] as $payload) {
        $n = $C::neutralize($payload);
        if (!str_starts_with($n, "'")) {
            $errors[] = 'formule non neutralisée : ' . json_encode($payload);
        }
    }
    foreach (['Dupont', 'a=b', '', '12', 'x-y'] as $safe) {
        if ($C::neutralize($safe) !== $safe) {
            $errors[] = "valeur sûre modifiée : « $safe »";
        }
    }

    $row = $C::row(['a;b', 'dit "oui"', "deux\nlignes", null, 5, '=cmd|\' /C calc\'!A0']);
    $expected = '"a;b";"dit ""oui""";"deux' . "\n" . 'lignes";;5;\'=cmd|\' /C calc\'!A0';
    if ($row !== $expected) {
        $errors[] = 'ligne CSV inattendue : ' . json_encode($row);
    }

    $csv = $C::build(['Nom', 'E-mail'], [['Éloïse', '=HYPERLINK("http://x")']]);
    if (!str_starts_with($csv, "\xEF\xBB\xBF")) {
        $errors[] = 'BOM UTF-8 manquant';
    }
    if (!str_contains($csv, "Nom;E-mail\r\n") || !str_ends_with($csv, "\r\n")) {
        $errors[] = 'en-tête ou fin de ligne CSV incorrects';
    }
    if (!str_contains($csv, "'=HYPERLINK(")) {
        $errors[] = 'formule HYPERLINK non neutralisée dans build()';
    }
    return $errors;
};
