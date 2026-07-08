<?php
declare(strict_types=1);

require_once dirname(__DIR__) . "/_helpers.php";

/** Bandeau de page intérieure. Variables : $title (obligatoire), $kicker, $lead, $back (['url'=>,'label'=>]). */
$kicker = (string) ($kicker ?? '');
$lead = (string) ($lead ?? '');
$back = is_array($back ?? null) ? $back : null;
?>
<section class="page-hero on-dark">
  <div class="container">
<?php if ($back): ?>
    <a class="page-hero__back" href="<?= e((string) $back['url']) ?>">&larr; <?= e((string) $back['label']) ?></a><br>
<?php endif; ?>
<?php if ($kicker !== ''): ?>
    <p class="kicker"><?= e($kicker) ?></p>
<?php endif; ?>
    <h1><?= e((string) $title) ?></h1>
<?php if ($lead !== ''): ?>
    <p class="page-hero__lead"><?= e($lead) ?></p>
<?php endif; ?>
  </div>
</section>
