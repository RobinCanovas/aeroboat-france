<?php
declare(strict_types=1);

use App\Admin\Ui;

/**
 * Champ de formulaire. Variables attendues : $name, $f (définition), $values, $errors.
 */
$type = (string) ($f['type'] ?? 'text');
$id = 'f-' . $name;
$val = $values[$name] ?? '';
$hasErr = isset($errors[$name]);
$describedBy = trim(($hasErr ? 'e-' . $name . ' ' : '') . (!empty($f['help']) ? 'h-' . $name : ''));
$attrs = ($hasErr ? ' aria-invalid="true"' : '') . ($describedBy !== '' ? ' aria-describedby="' . e($describedBy) . '"' : '');
$req = !empty($f['required']) ? ' required' : '';
?>
<div class="field<?= $hasErr ? ' has-error' : '' ?><?= $type === 'bool' ? ' field-check' : '' ?>">
<?php if ($type === 'bool') : ?>
    <input id="<?= e($id) ?>" name="<?= e($name) ?>" type="checkbox" value="1"<?= (int) $val === 1 ? ' checked' : '' ?><?= $attrs ?>>
    <label for="<?= e($id) ?>"><?= e((string) $f['label']) ?></label>
<?php else : ?>
    <label for="<?= e($id) ?>"><?= e((string) $f['label']) ?><?= !empty($f['required']) ? ' <span class="req" aria-hidden="true">*</span>' : '' ?></label>
    <?php if ($type === 'textarea' || $type === 'markdown') : ?>
        <textarea id="<?= e($id) ?>" name="<?= e($name) ?>" rows="<?= (int) ($f['rows'] ?? 5) ?>"<?= isset($f['max']) ? ' maxlength="' . (int) $f['max'] . '"' : '' ?><?= $req ?><?= $attrs ?>><?= e((string) $val) ?></textarea>
    <?php elseif ($type === 'select') : ?>
        <select id="<?= e($id) ?>" name="<?= e($name) ?>"<?= $req ?><?= $attrs ?>>
            <?php foreach ($f['options'] as $ov => $ol) : ?>
                <option value="<?= e((string) $ov) ?>"<?= (string) $val === (string) $ov ? ' selected' : '' ?>><?= e((string) $ol) ?></option>
            <?php endforeach; ?>
        </select>
    <?php elseif ($type === 'image') : ?>
        <?php if ($val !== '' && $val !== null) : ?>
            <p class="current-image">
                <img src="<?= e(Ui::pub('uploads/' . (string) $val)) ?>" alt="" width="120" height="120" loading="lazy">
                <span class="field-check"><input id="remove-<?= e($name) ?>" type="checkbox" name="remove_<?= e($name) ?>" value="1"> <label for="remove-<?= e($name) ?>">Retirer l'image actuelle</label></span>
            </p>
        <?php endif; ?>
        <input id="<?= e($id) ?>" name="<?= e($name) ?>" type="file" accept="image/jpeg,image/png,image/webp"<?= $attrs ?>>
    <?php elseif ($type === 'datetime') : ?>
        <input id="<?= e($id) ?>" name="<?= e($name) ?>" type="datetime-local" value="<?= e((string) $val) ?>"<?= $req ?><?= $attrs ?>>
    <?php elseif ($type === 'int') : ?>
        <input id="<?= e($id) ?>" name="<?= e($name) ?>" type="number" inputmode="numeric" step="1" value="<?= e((string) $val) ?>"<?= isset($f['min']) ? ' min="' . (int) $f['min'] . '"' : '' ?><?= isset($f['max']) ? ' max="' . (int) $f['max'] . '"' : '' ?><?= $req ?><?= $attrs ?>>
    <?php elseif ($type === 'password') : ?>
        <input id="<?= e($id) ?>" name="<?= e($name) ?>" type="password" autocomplete="new-password" minlength="12" value=""<?= $req ?><?= $attrs ?>>
    <?php else : ?>
        <input id="<?= e($id) ?>" name="<?= e($name) ?>" type="<?= $type === 'email' ? 'email' : ($type === 'url' ? 'url' : 'text') ?>" value="<?= e((string) $val) ?>"<?= isset($f['max']) ? ' maxlength="' . (int) $f['max'] . '"' : '' ?><?= $req ?><?= $attrs ?>>
    <?php endif; ?>
<?php endif; ?>
    <?php if (!empty($f['help'])) : ?><p class="hint" id="h-<?= e($name) ?>"><?= e((string) $f['help']) ?></p><?php endif; ?>
    <?php if ($hasErr) : ?><p class="error-text" id="e-<?= e($name) ?>"><?= e($errors[$name]) ?></p><?php endif; ?>
</div>
