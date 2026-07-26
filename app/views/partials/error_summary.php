<?php
declare(strict_types=1);

require_once dirname(__DIR__) . "/_helpers.php";

/**
 * Résumé d'erreurs (role="alert"), liens vers les champs concernés.
 * Variables : $errors (clé champ => clé de langue), $labels (clé champ => libellé traduit).
 * Les erreurs sans champ visible (jeton, trop rapide, limite…) s'affichent sans lien.
 */
$errors = is_array($errors ?? null) ? $errors : [];
$labels = is_array($labels ?? null) ? $labels : [];
if ($errors === []) {
    return;
}
?>
<div class="form-errors" role="alert" tabindex="-1" data-error-summary>
  <h2><?= e(t('forms.errors.summary_title', ['count' => count($errors)])) ?></h2>
  <ul>
<?php foreach ($errors as $field => $key): ?>
<?php if (isset($labels[$field])): ?>
    <li><a href="#f-<?= e((string) $field) ?>"><?= e((string) $labels[$field]) ?></a> : <?= e(t((string) $key)) ?></li>
<?php else: ?>
    <li><?= e(t((string) $key)) ?></li>
<?php endif; ?>
<?php endforeach; ?>
  </ul>
</div>
