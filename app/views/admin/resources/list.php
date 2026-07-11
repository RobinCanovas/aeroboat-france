<?php
declare(strict_types=1);

use App\Admin\Ui;

/** @var \App\Admin\Resource $res @var array $data @var array $query */
$def = $res->def;
$base = '/' . $res->key();
$extra = ['updated_at' => 'Modifié le', 'created_at' => 'Créé le', 'last_login_at' => 'Dernière connexion'];
$columns = [];
foreach ($def['list']['columns'] as $col) {
    $field = $def['fields'][$col] ?? ['type' => str_ends_with($col, '_at') ? 'datetime' : 'text', 'label' => $extra[$col] ?? $col];
    $columns[$col] = $field;
}
$sortable = $def['list']['sort'] ?? [];
$qs = static function (array $over) use ($data): string {
    $p = array_filter([
        'q' => $data['q'], 'sort' => $data['sort'], 'dir' => $data['dir'], 'page' => $data['page'] > 1 ? $data['page'] : null,
    ] + array_combine(array_map(static fn ($k) => 'f_' . $k, array_keys($data['filters'])), array_values($data['filters'])), static fn ($v) => $v !== null && $v !== '');
    $p = array_merge($p, $over);
    $p = array_filter($p, static fn ($v) => $v !== null && $v !== '');
    return $p === [] ? '' : '?' . http_build_query($p);
};
?>
<div class="page-head">
    <h1><?= e((string) $def['label']) ?></h1>
    <?php if ($res->canCreate()) : ?>
        <a class="btn btn-primary" href="<?= e(Ui::u($base . '/new')) ?>">Ajouter</a>
    <?php endif; ?>
</div>

<form method="get" action="<?= e(Ui::u($base)) ?>" class="filters card" role="search">
    <div class="field">
        <label for="q">Rechercher</label>
        <input id="q" name="q" type="search" value="<?= e($data['q']) ?>">
    </div>
    <?php foreach ($def['list']['filters'] ?? [] as $col => $label) : ?>
        <div class="field">
            <label for="f_<?= e($col) ?>"><?= e($label) ?></label>
            <select id="f_<?= e($col) ?>" name="f_<?= e($col) ?>">
                <option value="">Tous</option>
                <?php foreach ($def['fields'][$col]['options'] as $val => $text) : ?>
                    <option value="<?= e((string) $val) ?>"<?= ($data['filters'][$col] ?? '') === (string) $val ? ' selected' : '' ?>><?= e((string) $text) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endforeach; ?>
    <div class="actions"><button type="submit" class="btn">Filtrer</button></div>
</form>

<p class="hint"><?= (int) $data['total'] ?> résultat(s).</p>
<?php if ($data['rows']) : ?>
    <div class="table-wrap">
        <table class="data">
            <caption class="sr-only"><?= e((string) $def['label']) ?></caption>
            <thead>
                <tr>
                    <?php foreach ($columns as $col => $field) : ?>
                        <?php $isSort = $data['sort'] === $col; ?>
                        <th scope="col"<?= $isSort ? ' aria-sort="' . ($data['dir'] === 'asc' ? 'ascending' : 'descending') . '"' : '' ?>>
                            <?php if (in_array($col, $sortable, true)) : ?>
                                <a href="<?= e(Ui::u($base) . $qs(['sort' => $col, 'dir' => $isSort && $data['dir'] === 'asc' ? 'desc' : 'asc', 'page' => null])) ?>"><?= e((string) ($field['label'] ?? $col)) ?></a>
                            <?php else : ?>
                                <?= e((string) ($field['label'] ?? $col)) ?>
                            <?php endif; ?>
                        </th>
                    <?php endforeach; ?>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['rows'] as $row) : ?>
                    <tr>
                        <?php foreach ($columns as $col => $field) : ?>
                            <td data-label="<?= e((string) ($field['label'] ?? $col)) ?>"><?= Ui::cell($field, $row[$col] ?? '') ?></td>
                        <?php endforeach; ?>
                        <td class="row-actions">
                            <a href="<?= e(Ui::u($base . '/' . (int) $row['id'] . '/edit')) ?>">Modifier<span class="sr-only"> <?= e((string) ($row[$def['title_field']] ?? '')) ?></span></a>
                            <?php if ($res->canDelete()) : ?>
                                <a href="<?= e(Ui::u($base . '/' . (int) $row['id'] . '/delete')) ?>">Supprimer<span class="sr-only"> <?= e((string) ($row[$def['title_field']] ?? '')) ?></span></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else : ?>
    <p>Aucun enregistrement.</p>
<?php endif; ?>

<?php if ($data['pages'] > 1) : ?>
    <nav class="pagination" aria-label="Pagination">
        <?php if ($data['page'] > 1) : ?><a class="btn" href="<?= e(Ui::u($base) . $qs(['page' => $data['page'] - 1])) ?>">Précédent</a><?php endif; ?>
        <span>Page <?= (int) $data['page'] ?> sur <?= (int) $data['pages'] ?></span>
        <?php if ($data['page'] < $data['pages']) : ?><a class="btn" href="<?= e(Ui::u($base) . $qs(['page' => $data['page'] + 1])) ?>">Suivant</a><?php endif; ?>
    </nav>
<?php endif; ?>
