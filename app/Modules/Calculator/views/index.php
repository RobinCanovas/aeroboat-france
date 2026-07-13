<?php
declare(strict_types=1);

use App\Admin\Ui;
use App\Modules\Calculator\GroundEffect as G;

/** @var array $fields @var array $form @var array $errors @var ?array $result @var array $curve @var string $svg @var array $warnings */
$badges = ['known' => 'Donnée source', 'default' => 'Valeur usuelle', 'hypothesis' => 'Hypothèse : à renseigner'];
?>
<h1>Calculateur d'effet de sol</h1>
<p class="banner banner-warn" role="note"><strong>Modèle simplifié, résultat indicatif, non validé par des essais.</strong></p>
<p>Modèle de McCormick : φ = (16 h/b)² / (1 + (16 h/b)²) ; CDi,sol = φ · CL² / (π · AR · e) ; AR = b² / S ; CL = m·g / (q·S) ; q = ½ ρ v².</p>

<?php if ($errors) : ?>
    <div class="alert alert-error" role="alert">
        <p>Corrigez les champs suivants :</p>
        <ul>
            <?php foreach ($errors as $msg) : ?><li><?= e($msg) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="get" action="<?= e(Ui::u('/modules/calculator')) ?>" class="card form-grid" novalidate>
    <input type="hidden" name="calc" value="1">
    <?php foreach ($fields as $key => $f) : ?>
        <div class="field<?= isset($errors[$key]) ? ' has-error' : '' ?>">
            <label for="f-<?= e($key) ?>"><?= e($f['label']) ?><?= $f['unit'] !== '' ? ' (' . e($f['unit']) . ')' : '' ?></label>
            <input id="f-<?= e($key) ?>" name="<?= e($key) ?>" type="text" inputmode="decimal" autocomplete="off"
                value="<?= e($form[$key] ?? '') ?>" aria-describedby="h-<?= e($key) ?>"<?= isset($errors[$key]) ? ' aria-invalid="true"' : '' ?>>
            <p class="hint" id="h-<?= e($key) ?>">
                <span class="badge badge-<?= e($f['status']) ?>"><?= e($badges[$f['status']]) ?></span>
                <?= e($f['help']) ?> Entre <?= e(G::fmt($f['min'], 3)) ?> et <?= e(G::fmt($f['max'], 3)) ?>.
            </p>
        </div>
    <?php endforeach; ?>
    <div class="actions">
        <button type="submit" class="btn btn-primary">Calculer</button>
        <a class="btn" href="<?= e(Ui::u('/modules/calculator?preset=v6')) ?>">Préréglage V6 (indicatif)</a>
    </div>
</form>

<p class="hint">Préréglage V6 : ailes basses de 100 cm de chaque côté (envergure 2,0 m), corde 50 cm, masse inférieure à 2 kg (2,0 kg retenu). La vitesse, la hauteur et CD0 sont des <strong>hypothèses à renseigner</strong>, pas des mesures.</p>

<?php if ($result) : ?>
    <?php foreach ($warnings as $w) : ?><p class="alert alert-warn" role="note"><?= e($w) ?></p><?php endforeach; ?>
    <h2>Résultats</h2>
    <div class="table-wrap">
        <table class="data">
            <caption class="sr-only">Résultats du calcul</caption>
            <thead><tr><th scope="col">Grandeur</th><th scope="col">Hors effet de sol</th><th scope="col">En effet de sol</th></tr></thead>
            <tbody>
                <tr><th scope="row">Surface alaire S (m²)</th><td colspan="2"><?= e(G::fmt($result['S'], 3)) ?></td></tr>
                <tr><th scope="row">Allongement AR</th><td colspan="2"><?= e(G::fmt($result['AR'], 2)) ?></td></tr>
                <tr><th scope="row">Pression dynamique q (Pa)</th><td colspan="2"><?= e(G::fmt($result['q'], 1)) ?></td></tr>
                <tr><th scope="row">Coefficient de portance CL</th><td colspan="2"><?= e(G::fmt($result['CL'], 3)) ?></td></tr>
                <tr><th scope="row">Hauteur relative h/b</th><td colspan="2"><?= e(G::fmt($result['hb'], 3)) ?></td></tr>
                <tr><th scope="row">Facteur d'effet de sol φ</th><td colspan="2"><?= e(G::fmt($result['phi'], 3)) ?></td></tr>
                <tr><th scope="row">Traînée induite CDi</th><td><?= e(G::fmt($result['cdi_free'], 4)) ?></td><td><?= e(G::fmt($result['cdi_ground'], 4)) ?></td></tr>
                <tr><th scope="row">Réduction de traînée induite</th><td>0 %</td><td><?= e(G::fmt($result['reduction'], 1)) ?> %</td></tr>
                <tr><th scope="row">Traînée totale CD</th><td><?= e(G::fmt($result['cd_free'], 4)) ?></td><td><?= e(G::fmt($result['cd_ground'], 4)) ?></td></tr>
                <tr><th scope="row">Finesse L/D</th><td><?= e(G::fmt($result['ld_free'], 1)) ?></td><td><?= e(G::fmt($result['ld_ground'], 1)) ?></td></tr>
            </tbody>
        </table>
    </div>

    <h2>Finesse selon h/b</h2>
    <figure class="figure">
        <?= $svg ?>
        <figcaption>Trait plein : en effet de sol. Pointillés : hors effet de sol. Disque : point choisi. CL et CD0 supposés constants.</figcaption>
    </figure>
    <details>
        <summary>Valeurs de la courbe (tableau)</summary>
        <div class="table-wrap">
            <table class="data">
                <caption class="sr-only">Finesse L/D selon h/b</caption>
                <thead><tr><th scope="col">h/b</th><th scope="col">φ</th><th scope="col">L/D en effet de sol</th></tr></thead>
                <tbody>
                    <?php foreach ($curve as $i => $p) : ?>
                        <?php if (($i + 1) % 5 === 0 || $i === 0) : ?>
                            <tr><td><?= e(G::fmt($p['hb'], 2)) ?></td><td><?= e(G::fmt($p['phi'], 3)) ?></td><td><?= e(G::fmt($p['ld'], 1)) ?></td></tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </details>
<?php endif; ?>
