<?php
declare(strict_types=1);

/** Calculateur d'effet de sol : formule de McCormick, cohérence des sorties, validation stricte, SVG accessible. */
return static function (): array {
    require_once ROOT . '/app/Modules/Calculator/GroundEffect.php';
    $G = \App\Modules\Calculator\GroundEffect::class;
    $errors = [];
    $near = static fn (float $a, float $b, float $eps): bool => abs($a - $b) <= $eps;

    // φ(h/b = 0,1) = 2,56 / 3,56 ≈ 0,719
    $phi = $G::phi(0.1, 1.0);
    if (!$near($phi, 0.7191, 0.0005)) {
        $errors[] = "φ(h/b=0,1) attendu ≈ 0,719, obtenu $phi";
    }
    if (!$near($G::phi(0.2, 2.0), $phi, 1e-12)) {
        $errors[] = 'φ ne dépend que de h/b';
    }
    if ($G::phi(0.0001, 1.0) > 0.001 || $G::phi(5.0, 1.0) < 0.999) {
        $errors[] = 'φ doit tendre vers 0 près du sol et vers 1 loin du sol';
    }

    // Préréglage V6 : envergure 2,0 ; corde 0,5 ; masse 2,0 (valeurs sourcées) + hypothèses.
    [$v, $err] = $G::parse($G::presetV6());
    if ($err !== []) {
        $errors[] = 'le préréglage V6 doit être valide : ' . implode(' | ', $err);
        return $errors;
    }
    $r = $G::compute($v);
    if (!$near($r['S'], 1.0, 1e-9) || !$near($r['AR'], 4.0, 1e-9)) {
        $errors[] = 'S = 1,0 m² et AR = 4,0 attendus pour 2,0 m × 0,5 m';
    }
    $q = 0.5 * 1.225 * 10 ** 2;
    if (!$near($r['q'], $q, 1e-9) || !$near($r['CL'], 2.0 * 9.81 / ($q * 1.0), 1e-9)) {
        $errors[] = 'q et CL incohérents avec q = ½ρv² et CL = mg/(qS)';
    }
    if (!$near($r['cdi_ground'], $r['phi'] * $r['cdi_free'], 1e-12)) {
        $errors[] = 'CDi,sol doit valoir φ · CDi libre';
    }
    if (!$near($r['reduction'], (1 - $r['phi']) * 100, 1e-9) || $r['reduction'] <= 0) {
        $errors[] = 'réduction de traînée induite = (1 − φ) × 100';
    }
    if (!($r['ld_ground'] > $r['ld_free'])) {
        $errors[] = 'la finesse en effet de sol doit dépasser la finesse libre';
    }
    $cdi = $r['CL'] ** 2 / (M_PI * 4.0 * 0.8);
    if (!$near($r['cdi_free'], $cdi, 1e-12)) {
        $errors[] = 'CDi libre = CL² / (π·AR·e)';
    }

    // Courbe et SVG accessible.
    $curve = $G::curve($v, 50);
    if (count($curve) !== 50 || $curve[0]['ld'] < $curve[49]['ld']) {
        $errors[] = 'la finesse doit décroître quand h/b augmente';
    }
    $svg = $G::svg($curve, $r);
    foreach (['<svg', '<title', '<desc', 'role="img"', 'aria-labelledby'] as $needle) {
        if (!str_contains($svg, $needle)) {
            $errors[] = "SVG : « $needle » manquant";
        }
    }
    if (str_contains($svg, 'style=') || str_contains($svg, '<script')) {
        $errors[] = 'SVG : ni style en ligne ni script (CSP)';
    }

    // Validation stricte.
    $bad = $G::presetV6();
    $cases = [
        ['span', 'abc'], ['span', '-1'], ['span', '1e3'], ['span', ''], ['span', '0'], ['span', '999'],
        ['mass', '0'], ['speed', '0,5'], ['rho', '3'], ['e', '1,5'], ['cd0', '0'], ['height', '10'],
    ];
    foreach ($cases as [$k, $val]) {
        $in = array_merge($bad, [$k => $val]);
        [, $e] = $G::parse($in);
        if (!isset($e[$k])) {
            $errors[] = "validation : $k = « $val » aurait dû être refusé";
        }
    }
    [, $e] = $G::parse(array_merge($bad, ['chord' => '3', 'span' => '2']));
    if (!isset($e['chord'])) {
        $errors[] = 'la corde ne peut pas dépasser l\'envergure';
    }
    [$ok, $e] = $G::parse(array_merge($bad, ['span' => '2.5', 'chord' => '0,5']));
    if ($e !== [] || !$near($ok['span'], 2.5, 1e-9)) {
        $errors[] = 'virgule et point décimaux doivent être acceptés';
    }
    [, $e] = $G::parse(['span' => ['x']]);
    if (!isset($e['span'])) {
        $errors[] = 'une valeur tableau doit être refusée';
    }
    return $errors;
};
