<?php
declare(strict_types=1);

use App\Admin\Ui;

/** @var \App\Admin\Resource $res @var ?array $row @var array $values @var array<string,string> $errors @var array<string,string> $previews @var string $action */
$def = $res->def;
$fields = $def['fields'];
$title = $row === null ? 'Nouveau : ' . $def['singular'] : 'Modifier : ' . $def['singular'];
$hasPreview = !empty($def['preview']);
?>
<div class="page-head">
    <h1><?= e(ucfirst($title)) ?></h1>
    <a class="btn" href="<?= e(Ui::u('/' . $res->key())) ?>">Retour à la liste</a>
</div>

<?php if ($errors) : ?>
    <div class="alert alert-error" role="alert">
        <p>Le formulaire contient des erreurs :</p>
        <ul>
            <?php foreach ($errors as $name => $msg) : ?>
                <li><a href="#f-<?= e($name) ?>"><?= e($msg) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" class="stack" novalidate>
    <?= Ui::csrf() ?>
    <?php foreach ($def['groups'] as $group) : ?>
        <fieldset class="card<?= isset($group['class']) ? ' ' . e($group['class']) : '' ?>">
            <legend><?= e($group['title']) ?></legend>
            <?php foreach ($group['fields'] as $name) : ?>
                <?php $f = $fields[$name]; include __DIR__ . '/../parts/field.php'; ?>
                <?php if (isset($previews[$name])) : ?>
                    <div class="preview" aria-label="Aperçu"><?= $previews[$name] ?></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </fieldset>
    <?php endforeach; ?>

    <div class="actions">
        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <?php if ($hasPreview) : ?>
            <button type="submit" class="btn" name="preview" value="1" formnovalidate>Aperçu Markdown</button>
        <?php endif; ?>
        <a class="btn" href="<?= e(Ui::u('/' . $res->key())) ?>">Annuler</a>
    </div>
    <?php if ($hasPreview) : ?>
        <p class="hint">L'aperçu n'enregistre rien et n'envoie pas de fichier : renvoyez l'image à l'enregistrement.</p>
    <?php endif; ?>
</form>
