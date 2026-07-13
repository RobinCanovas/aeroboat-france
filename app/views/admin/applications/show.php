<?php
declare(strict_types=1);

use App\Admin\Time;
use App\Admin\Ui;

/** @var array<string,mixed> $a @var bool $hasCv */
$id = (int) $a['id'];
$row = static function (string $label, string $html): string {
    return '<div class="dl-row"><dt>' . e($label) . '</dt><dd>' . $html . '</dd></div>';
};
$text = static fn (?string $s): string => $s === null || $s === '' ? '<span class="hint">Non renseigné</span>' : nl2br(e($s), false);
?>
<div class="page-head">
    <h1>Candidature de <?= e($a['first_name'] . ' ' . $a['last_name']) ?></h1>
    <a class="btn" href="<?= e(Ui::u('/candidatures')) ?>">Retour à la liste</a>
</div>

<dl class="card dl">
    <?= $row('Reçue le', e(Time::utcToParis((string) $a['created_at']))) ?>
    <?= $row('Statut actuel', '<span class="badge badge-st-' . e((string) $a['status']) . '">' . e(Ui::statusLabel((string) $a['status'])) . '</span>') ?>
    <?= $row('E-mail', '<a href="mailto:' . e((string) $a['email']) . '">' . e((string) $a['email']) . '</a>') ?>
    <?= $row('École', $text($a['school'] ?? null)) ?>
    <?= $row('Niveau', $text($a['level'] ?? null)) ?>
    <?= $row('Compétences', $text($a['skills'] ?? null)) ?>
    <?= $row('Motivation', $text($a['motivation'] ?? null)) ?>
    <?= $row('Disponibilité', $text($a['availability'] ?? null)) ?>
    <?= $row('Langue du formulaire', e(strtoupper((string) $a['locale']))) ?>
    <?= $row('Consentement donné le', e(Time::utcToParis((string) $a['consent_at']))) ?>
    <?= $row('CV', $hasCv ? '<a href="' . e(Ui::u('/candidatures/' . $id . '/cv')) . '">Télécharger le CV (PDF)</a>' : '<span class="hint">Aucun CV</span>') ?>
</dl>

<form method="post" action="<?= e(Ui::u('/candidatures/' . $id)) ?>" class="card stack">
    <?= Ui::csrf() ?>
    <h2>Suivi</h2>
    <div class="field">
        <label for="status">Statut</label>
        <select id="status" name="status">
            <?php foreach (Ui::STATUS_APPLICATION as $key => $label) : ?>
                <option value="<?= e($key) ?>"<?= $a['status'] === $key ? ' selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label for="notes">Notes internes (non visibles du candidat)</label>
        <textarea id="notes" name="notes" rows="6" maxlength="5000"><?= e((string) ($a['notes'] ?? '')) ?></textarea>
    </div>
    <div class="actions">
        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a class="btn btn-danger-outline" href="<?= e(Ui::u('/candidatures/' . $id . '/delete')) ?>">Supprimer la candidature</a>
    </div>
</form>
