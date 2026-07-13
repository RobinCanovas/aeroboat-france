<?php
declare(strict_types=1);

use App\Admin\Time;
use App\Admin\Ui;

/** @var list<array> $rows @var int $total @var int $page @var int $pages @var string $status @var string $q @var array<string,int> $counts */
$qs = static function (array $over) use ($status, $q): string {
    $p = array_merge(['status' => $status, 'q' => $q], $over);
    $p = array_filter($p, static fn ($v) => $v !== null && $v !== '');
    return $p === [] ? '' : '?' . http_build_query($p);
};
?>
<div class="page-head">
    <h1>Candidatures</h1>
    <a class="btn" href="<?= e(Ui::u('/candidatures/export') . $qs([])) ?>">Exporter en CSV<?= $status !== '' || $q !== '' ? ' (filtre actuel)' : '' ?></a>
</div>

<nav class="tabs" aria-label="Filtrer par statut">
    <ul>
        <li><a href="<?= e(Ui::u('/candidatures') . $qs(['status' => null])) ?>"<?= $status === '' ? ' aria-current="page"' : '' ?>>Toutes (<?= (int) array_sum($counts) ?>)</a></li>
        <?php foreach (Ui::STATUS_APPLICATION as $key => $label) : ?>
            <li><a href="<?= e(Ui::u('/candidatures') . $qs(['status' => $key])) ?>"<?= $status === $key ? ' aria-current="page"' : '' ?>><?= e($label) ?> (<?= (int) ($counts[$key] ?? 0) ?>)</a></li>
        <?php endforeach; ?>
    </ul>
</nav>

<form method="get" action="<?= e(Ui::u('/candidatures')) ?>" class="filters card" role="search">
    <?php if ($status !== '') : ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
    <div class="field">
        <label for="q">Rechercher (nom, e-mail, école)</label>
        <input id="q" name="q" type="search" value="<?= e($q) ?>">
    </div>
    <div class="actions"><button type="submit" class="btn">Rechercher</button></div>
</form>

<p class="hint"><?= (int) $total ?> candidature(s).</p>
<?php if ($rows) : ?>
    <div class="table-wrap">
        <table class="data">
            <caption class="sr-only">Candidatures</caption>
            <thead><tr><th scope="col">Reçue le</th><th scope="col">Candidat</th><th scope="col">École</th><th scope="col">Statut</th><th scope="col">CV</th></tr></thead>
            <tbody>
                <?php foreach ($rows as $a) : ?>
                    <tr>
                        <td data-label="Reçue le"><?= e(Time::utcToParis((string) $a['created_at'])) ?></td>
                        <td data-label="Candidat"><a href="<?= e(Ui::u('/candidatures/' . (int) $a['id'])) ?>"><?= e($a['first_name'] . ' ' . $a['last_name']) ?></a><br><span class="hint"><?= e((string) $a['email']) ?></span></td>
                        <td data-label="École"><?= e((string) ($a['school'] ?? '')) ?></td>
                        <td data-label="Statut"><span class="badge badge-st-<?= e((string) $a['status']) ?>"><?= e(Ui::statusLabel((string) $a['status'])) ?></span></td>
                        <td data-label="CV"><?= ($a['cv_file'] ?? '') !== '' ? 'Oui' : 'Non' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else : ?>
    <p>Aucune candidature.</p>
<?php endif; ?>

<?php if ($pages > 1) : ?>
    <nav class="pagination" aria-label="Pagination">
        <?php if ($page > 1) : ?><a class="btn" href="<?= e(Ui::u('/candidatures') . $qs(['page' => $page - 1])) ?>">Précédent</a><?php endif; ?>
        <span>Page <?= (int) $page ?> sur <?= (int) $pages ?></span>
        <?php if ($page < $pages) : ?><a class="btn" href="<?= e(Ui::u('/candidatures') . $qs(['page' => $page + 1])) ?>">Suivant</a><?php endif; ?>
    </nav>
<?php endif; ?>
