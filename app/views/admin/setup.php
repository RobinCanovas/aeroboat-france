<?php
declare(strict_types=1);

use App\Admin\Ui;

/** @var array<string,string> $errors @var array<string,string> $old */
$err = static function (string $k) use ($errors): string {
    return isset($errors[$k]) ? '<p class="error-text" id="e-' . e($k) . '">' . e($errors[$k]) . '</p>' : '';
};
?>
<div class="auth-box">
    <h1>Installation de l'espace membres</h1>
    <p>Cette page crée les tables, charge les contenus de départ et le premier compte administrateur. Elle disparaît dès qu'un compte existe.</p>
    <?php if ($errors) : ?><p class="alert alert-error" role="alert">Le formulaire contient des erreurs.</p><?php endif; ?>
    <form method="post" action="<?= e(Ui::u('/setup')) ?>" class="card stack" autocomplete="off">
        <?= Ui::csrf() ?>
        <div class="field">
            <label for="setup_token">Jeton d'installation (SETUP_TOKEN)</label>
            <input id="setup_token" name="setup_token" type="password" required autocomplete="off">
            <?= $err('setup_token') ?>
        </div>
        <div class="field">
            <label for="username">Identifiant</label>
            <input id="username" name="username" type="text" value="<?= e($old['username']) ?>" autocapitalize="none" spellcheck="false" required>
            <?= $err('username') ?>
        </div>
        <div class="field">
            <label for="display_name">Nom affiché</label>
            <input id="display_name" name="display_name" type="text" value="<?= e($old['display_name']) ?>" required>
            <?= $err('display_name') ?>
        </div>
        <div class="field">
            <label for="email">E-mail (facultatif)</label>
            <input id="email" name="email" type="email" value="<?= e($old['email']) ?>" autocomplete="off">
            <?= $err('email') ?>
        </div>
        <div class="field">
            <label for="password">Mot de passe (12 caractères minimum)</label>
            <input id="password" name="password" type="password" autocomplete="new-password" minlength="12" required>
            <?= $err('password') ?>
        </div>
        <div class="field">
            <label for="password_confirm">Confirmation du mot de passe</label>
            <input id="password_confirm" name="password_confirm" type="password" autocomplete="new-password" minlength="12" required>
        </div>
        <button type="submit" class="btn btn-primary">Installer</button>
    </form>
</div>
