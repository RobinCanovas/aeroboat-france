<?php
declare(strict_types=1);

require_once dirname(__DIR__) . "/_helpers.php";

/**
 * Timeline à statuts (icône + texte, jamais la couleur seule). Lisible sans JS.
 * Variables : $items (liste de ['date','title','text','status']), $horizontal (bool, 4 colonnes dès 1280 px).
 */
$items = is_array($items ?? null) ? $items : [];
$horizontal = (bool) ($horizontal ?? false);
$cols = max(2, min(5, count($items)));
?>
<ol class="timeline<?= $horizontal ? ' timeline--h timeline--cols-' . $cols : '' ?>">
<?php foreach ($items as $i => $item): ?>
<?php $st = ui_status((string) ($item['status'] ?? '')); ?>
  <li class="tl-item tl-item--<?= $st ?> reveal">
    <span class="tl-item__mark"><?= ui_icon($st) ?></span>
    <div class="tl-item__card">
      <div class="tl-item__meta">
<?php if ((string) ($item['date'] ?? '') !== ''): ?>
        <span class="tl-item__date"><?= e((string) $item['date']) ?></span>
<?php endif; ?>
        <?= ui_status_badge($st) ?>
      </div>
      <h3 class="tl-item__title"><?= e((string) ($item['title'] ?? '')) ?></h3>
<?php if ((string) ($item['text'] ?? '') !== ''): ?>
      <p><?= e((string) $item['text']) ?></p>
<?php endif; ?>
    </div>
  </li>
<?php endforeach; ?>
</ol>
