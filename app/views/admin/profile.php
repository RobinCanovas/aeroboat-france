<?php
declare(strict_types=1);

use App\Admin\Policy;
use App\Admin\Ui;

/** @var string $name @var array $row @var array<string,string> $errors */
$err = static function (string $k) use ($errors): string {
    return isset($errors[$k]) ? '<p class="error-text" id="e-' . e($k) . '">' . e($errors[$k]) . '</p>' : '';
};
?>
<h1>Mon profil</h1>
<p>Identifiant : <strong><?= e((string) $row['username']) ?></strong> · Rôle : <?= e(Policy::ROLES[$row['role']] ?? (string) $row['role']) ?></p>
<?php if ($errors) : ?><p class="alert alert-error" role="alert">Le formulaire contient des erreurs.</p><?php endif; ?>
<form method="post" action="<?= e(Ui::u('/profil')) ?>" class="card stack" autocomplete="off">
    <?= Ui::csrf() ?>
    <div class="field">
        <label for="display_name">Nom affiché</label>
        <input id="display_name" name="display_name" type="text" value="<?= e($name) ?>" required maxlength="120">
        <?= $err('display_name') ?>
    </div>
    <fieldset>
        <legend>Changer le mot de passe</legend>
        <p class="hint">Laissez ces champs vides pour conserver le mot de passe actuel.</p>
        <div class="field">
            <label for="current_password">Mot de passe actuel</label>
            <input id="current_password" name="current_password" type="password" autocomplete="current-password">
            <?= $err('current_password') ?>
        </div>
        <div class="field">
            <label for="new_password">Nouveau mot de passe (12 caractères minimum)</label>
            <input id="new_password" name="new_password" type="password" autocomplete="new-password" minlength="12">
            <?= $err('new_password') ?>
        </div>
        <div class="field">
            <label for="new_password_confirm">Confirmation du nouveau mot de passe</label>
            <input id="new_password_confirm" name="new_password_confirm" type="password" autocomplete="new-password" minlength="12">
        </div>
    </fieldset>
    <div class="actions"><button type="submit" class="btn btn-primary">Enregistrer</button></div>
</form>
