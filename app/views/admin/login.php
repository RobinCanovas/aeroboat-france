<?php
declare(strict_types=1);

use App\Admin\Ui;

/** @var string $error @var string $username */
?>
<div class="auth-box">
    <h1>Connexion</h1>
    <?php if ($error !== '') : ?><p class="alert alert-error" role="alert"><?= e($error) ?></p><?php endif; ?>
    <form method="post" action="<?= e(Ui::u('/login')) ?>" class="card stack">
        <?= Ui::csrf() ?>
        <div class="field">
            <label for="username">Identifiant</label>
            <input id="username" name="username" type="text" value="<?= e($username) ?>" autocomplete="username" autocapitalize="none" spellcheck="false" required autofocus>
        </div>
        <div class="field">
            <label for="password">Mot de passe</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
        </div>
        <button type="submit" class="btn btn-primary">Se connecter</button>
    </form>
</div>
