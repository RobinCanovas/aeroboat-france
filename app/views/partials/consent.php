<?php
declare(strict_types=1);

require_once dirname(__DIR__) . "/_helpers.php";

/** Case de consentement obligatoire. Variables : $labelHtml (HTML de confiance déjà produit par t_html()), $errors. */
$errors = is_array($errors ?? null) ? $errors : [];
$errKey = isset($errors['consent']) ? (string) $errors['consent'] : '';
$describedBy = $errKey !== '' ? ' aria-describedby="f-consent-err" aria-invalid="true"' : '';
$checked = (string) old('consent') !== '' ? ' checked' : '';
?>
<div class="field">
  <div class="check">
    <input type="checkbox" id="f-consent" name="consent" value="1" required aria-required="true"<?= $describedBy ?><?= $checked ?>>
    <label for="f-consent"><?= $labelHtml ?></label>
  </div>
<?php if ($errKey !== ''): ?>
  <p class="field__error" id="f-consent-err"><?= ui_icon('alert') ?><span><?= e(t($errKey)) ?></span></p>
<?php endif; ?>
</div>
