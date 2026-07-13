<?php
declare(strict_types=1);

use App\Admin\Ui;

/** @var string $message @var string $action @var string $cancel @var string $button */
?>
<h1>Confirmer la suppression</h1>
<p class="alert alert-warn" role="alert"><?= e($message) ?></p>
<form method="post" action="<?= e($action) ?>" class="actions">
    <?= Ui::csrf() ?>
    <button type="submit" class="btn btn-danger"><?= e($button) ?></button>
    <a class="btn" href="<?= e($cancel) ?>">Annuler</a>
</form>
