<?php
declare(strict_types=1);

use App\Admin\Time;
use App\Admin\Ui;

/** @var list<array> $rows @var string $kind @var string $state @var string $type @var array<string,string> $rightsTypes */
$qs = static function (array $over) use ($kind, $state, $type): string {
    $p = array_filter(array_merge(['kind' => $kind, 'state' => $state, 'type' => $kind === 'rights' ? $type : ''], $over), static fn ($v) => $v !== null && $v !== '');
    return $p === [] ? '' : '?' . http_build_query($p);
};
?>
<h1>Messages</h1>

<nav class="tabs" aria-label="Filtrer les messages">
    <ul>
        <li><a href="<?= e(Ui::u('/messages') . $qs(['kind' => null])) ?>"<?= $kind === '' ? ' aria-current="page"' : '' ?>>Tous</a></li>
        <li><a href="<?= e(Ui::u('/messages') . $qs(['kind' => 'contact'])) ?>"<?= $kind === 'contact' ? ' aria-current="page"' : '' ?>>Contact</a></li>
        <li><a href="<?= e(Ui::u('/messages') . $qs(['kind' => 'rights'])) ?>"<?= $kind === 'rights' ? ' aria-current="page"' : '' ?>>Demandes de droits</a></li>
    </ul>
    <ul>
        <li><a href="<?= e(Ui::u('/messages') . $qs(['state' => null])) ?>"<?= $state === '' ? ' aria-current="page"' : '' ?>>Tous états</a></li>
        <li><a href="<?= e(Ui::u('/messages') . $qs(['state' => 'open'])) ?>"<?= $state === 'open' ? ' aria-current="page"' : '' ?>>À traiter</a></li>
        <li><a href="<?= e(Ui::u('/messages') . $qs(['state' => 'done'])) ?>"<?= $state === 'done' ? ' aria-current="page"' : '' ?>>Traités</a></li>
    </ul>
    <?php if ($kind === 'rights') : ?>
    <ul aria-label="Type de demande">
        <li><a href="<?= e(Ui::u('/messages') . $qs(['type' => null])) ?>"<?= $type === '' ? ' aria-current="page"' : '' ?>>Tous types</a></li>
        <?php foreach ($rightsTypes as $tk => $tl) : ?>
            <li><a href="<?= e(Ui::u('/messages') . $qs(['type' => $tk])) ?>"<?= $type === $tk ? ' aria-current="page"' : '' ?>><?= e($tl) ?></a></li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</nav>

<?php if (!$rows) : ?><p>Aucun message.</p><?php endif; ?>
<ul class="messages">
    <?php foreach ($rows as $m) : ?>
        <?php $isRights = $m['kind'] === 'rights'; $done = (int) $m['handled'] === 1; ?>
        <li class="card message<?= $isRights ? ' message-rights' : '' ?><?= $done ? ' message-done' : '' ?>">
            <header>
                <h2>
                    <?php if ($isRights) : ?><span class="badge badge-rights">Demande de droits<?= isset($rightsTypes[(string) $m['request_type']]) ? ' : ' . e($rightsTypes[(string) $m['request_type']]) : '' ?></span>
                    <?php else : ?><span class="badge">Contact</span><?php endif; ?>
                    <?= e((string) ($m['subject'] ?? '') !== '' ? (string) $m['subject'] : 'Sans objet') ?>
                </h2>
                <p class="hint">De <?= e((string) $m['name']) ?> (<a href="mailto:<?= e((string) $m['email']) ?>"><?= e((string) $m['email']) ?></a>), reçu le <?= e(Time::utcToParis((string) $m['created_at'])) ?> · <?= $done ? 'Traité' : 'À traiter' ?></p>
                <?php if ($isRights && !$done && $m['deadline'] !== null) : ?>
                    <p class="alert <?= $m['days_left'] < 0 ? 'alert-error' : 'alert-warn' ?>" role="note">
                        Réponse due avant le <strong><?= e((string) $m['deadline']) ?></strong> (délai légal d'un mois)
                        <?= $m['days_left'] < 0 ? ' : délai dépassé de ' . (int) abs($m['days_left']) . ' jour(s).' : ' : il reste ' . (int) $m['days_left'] . ' jour(s).' ?>
                    </p>
                <?php endif; ?>
            </header>
            <p><?= nl2br(e((string) $m['body']), false) ?></p>
            <div class="actions">
                <form method="post" action="<?= e(Ui::u('/messages/' . (int) $m['id'] . '/handled')) ?>" class="inline-form">
                    <?= Ui::csrf() ?>
                    <input type="hidden" name="handled" value="<?= $done ? '0' : '1' ?>">
                    <input type="hidden" name="kind" value="<?= e($kind) ?>">
                    <input type="hidden" name="state" value="<?= e($state) ?>">
                    <button type="submit" class="btn"><?= $done ? 'Remettre à traiter' : 'Marquer comme traité' ?></button>
                </form>
                <a class="btn btn-danger-outline" href="<?= e(Ui::u('/messages/' . (int) $m['id'] . '/delete')) ?>">Supprimer</a>
            </div>
        </li>
    <?php endforeach; ?>
</ul>
