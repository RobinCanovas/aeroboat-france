<?php
declare(strict_types=1);

use App\Admin\Ui;

/** @var int $messages @var int $applications @var array{messages:int,applications:int} $months */
?>
<h1>Purge RGPD</h1>
<p>Durées de conservation appliquées : les messages sont gardés <?= (int) $months['messages'] ?> mois et les candidatures <?= (int) $months['applications'] ?> mois. La purge supprime définitivement les données plus anciennes, CV compris.</p>

<div class="table-wrap">
    <table class="data">
        <caption class="sr-only">Éléments à purger</caption>
        <thead><tr><th scope="col">Type</th><th scope="col">Ancienneté</th><th scope="col">À supprimer</th></tr></thead>
        <tbody>
            <tr><th scope="row">Messages</th><td>plus de <?= (int) $months['messages'] ?> mois</td><td><?= (int) $messages ?></td></tr>
            <tr><th scope="row">Candidatures (et CV)</th><td>plus de <?= (int) $months['applications'] ?> mois</td><td><?= (int) $applications ?></td></tr>
        </tbody>
    </table>
</div>

<?php if ($messages + $applications > 0) : ?>
    <form method="post" action="<?= e(Ui::u('/rgpd')) ?>" class="card stack">
        <?= Ui::csrf() ?>
        <div class="field field-check">
            <input id="confirm" name="confirm" type="checkbox" value="1" required>
            <label for="confirm">Je confirme la suppression définitive de ces <?= (int) ($messages + $applications) ?> élément(s).</label>
        </div>
        <div class="actions"><button type="submit" class="btn btn-danger">Lancer la purge</button></div>
    </form>
<?php else : ?>
    <p class="alert alert-ok" role="status">Rien à purger pour le moment.</p>
<?php endif; ?>
