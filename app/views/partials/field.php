<?php
declare(strict_types=1);

require_once dirname(__DIR__) . "/_helpers.php";

/**
 * Champ de formulaire accessible.
 * Variables : $name, $label, $type (text|email|tel|textarea|select|file), $required (bool),
 *   $errors (clé champ => clé de langue), $hint (texte déjà traduit), $options (valeur => libellé, pour select),
 *   $maxlength, $autocomplete, $rows, $accept.
 * Erreur liée par aria-describedby, aria-invalid sur le contrôle. La valeur vient de old().
 */
$type = (string) ($type ?? 'text');
$required = (bool) ($required ?? false);
$errors = is_array($errors ?? null) ? $errors : [];
$hint = (string) ($hint ?? '');
$id = 'f-' . $name;
$errKey = isset($errors[$name]) ? (string) $errors[$name] : '';
$describedBy = [];
if ($hint !== '') {
    $describedBy[] = $id . '-hint';
}
if ($errKey !== '') {
    $describedBy[] = $id . '-err';
}
$attrs = ' id="' . e($id) . '" name="' . e((string) $name) . '" class="field__control"';
if ($required) {
    $attrs .= ' required aria-required="true"';
}
if ($errKey !== '') {
    $attrs .= ' aria-invalid="true"';
}
if ($describedBy) {
    $attrs .= ' aria-describedby="' . e(implode(' ', $describedBy)) . '"';
}
if (!empty($maxlength)) {
    $attrs .= ' maxlength="' . (int) $maxlength . '"';
}
if (!empty($autocomplete)) {
    $attrs .= ' autocomplete="' . e((string) $autocomplete) . '"';
}
$value = (string) old((string) $name);
?>
<div class="field">
  <label class="field__label" for="<?= e($id) ?>"><?= e((string) $label) ?><?php if ($required): ?><span class="field__req" aria-hidden="true">*</span><?php endif; ?></label>
<?php if ($hint !== ''): ?>
  <p class="field__hint" id="<?= e($id) ?>-hint"><?= e($hint) ?></p>
<?php endif; ?>
<?php if ($type === 'textarea'): ?>
  <textarea<?= $attrs ?> rows="<?= (int) ($rows ?? 6) ?>"><?= e($value) ?></textarea>
<?php elseif ($type === 'select'): ?>
  <select<?= $attrs ?>>
    <option value="" disabled<?= $value === '' ? ' selected' : '' ?>><?= e(t('forms.choose')) ?></option>
<?php foreach (($options ?? []) as $optValue => $optLabel): ?>
    <option value="<?= e((string) $optValue) ?>"<?= $value === (string) $optValue ? ' selected' : '' ?>><?= e((string) $optLabel) ?></option>
<?php endforeach; ?>
  </select>
<?php elseif ($type === 'file'): ?>
  <input type="file"<?= $attrs ?><?= !empty($accept) ? ' accept="' . e((string) $accept) . '"' : '' ?>>
<?php else: ?>
  <input type="<?= e($type) ?>"<?= $attrs ?> value="<?= e($value) ?>">
<?php endif; ?>
<?php if ($errKey !== ''): ?>
  <p class="field__error" id="<?= e($id) ?>-err"><?= ui_icon('alert') ?><span><?= e(t($errKey)) ?></span></p>
<?php endif; ?>
</div>
