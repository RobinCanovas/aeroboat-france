<?php
declare(strict_types=1);

use App\Core\View;

require_once dirname(__DIR__) . '/_helpers.php';

$objectives = ui_list('project.objectives.items', ['title', 'text'], 8);
$v6 = ui_list('project.v6.items', ['title', 'text'], 10);
$timeline = ui_list('project.timeline.items', ['date', 'title', 'text', 'status'], 8);
$gallery = [
    'water-run', 'lake-alpine', 'model-water', 'hold-red', 'ribs-ground', 'wing-closeup',
    'wing-pavement', 'dusk-lake', 'cfd-streams', 'cfd-color', 'render-dark', 'render-blue',
];
?>
<?= View::partial('partials/page_hero', [
    'kicker' => t('project.hero.kicker'),
    'title' => t('project.hero.title'),
    'lead' => t('project.hero.lead'),
]) ?>

<section class="section" aria-labelledby="why-title">
  <div class="container split split--reverse">
    <div class="reveal">
      <h2 id="why-title"><?= e(t('project.why.title')) ?></h2>
      <p><?= e(t('project.why.text')) ?></p>
      <ul class="version-list">
        <li><?= e(t('project.why.v3')) ?></li>
        <li><?= e(t('project.why.v4')) ?></li>
        <li><?= e(t('project.why.v6')) ?></li>
      </ul>
    </div>
    <figure class="split__media reveal d1">
      <?= img_tag('ribs-lawn', t('img.ribs-lawn'), '(min-width: 48em) 50vw, 100vw') ?>
    </figure>
  </div>
</section>

<?php if ($objectives): ?>
<section class="section section--surface" aria-labelledby="obj-title">
  <div class="container">
    <div class="section__head"><h2 id="obj-title"><?= e(t('project.objectives.title')) ?></h2></div>
    <ol class="cards cards--3">
<?php foreach ($objectives as $i => $o): ?>
      <li class="card reveal d<?= $i % 3 ?>">
        <span class="card__num" aria-hidden="true"><?= sprintf('%02d', $i + 1) ?></span>
        <h3><?= e($o['title']) ?></h3>
        <p><?= e($o['text']) ?></p>
      </li>
<?php endforeach; ?>
    </ol>
  </div>
</section>
<?php endif; ?>

<section class="section" aria-labelledby="v6-title">
  <div class="container split">
    <figure class="split__media reveal">
      <?= img_tag('cgi-v-red', t('img.cgi-v-red'), '(min-width: 48em) 50vw, 100vw') ?>
    </figure>
    <div class="reveal d1">
      <h2 id="v6-title"><?= e(t('project.v6.title')) ?></h2>
      <p class="section__lead"><?= e(t('project.v6.lead')) ?></p>
<?php if ($v6): ?>
      <ul class="version-list">
<?php foreach ($v6 as $item): ?>
        <li><strong><?= e($item['title']) ?></strong><?= $item['text'] !== '' ? ' ' . e($item['text']) : '' ?></li>
<?php endforeach; ?>
      </ul>
<?php endif; ?>
      <div class="note"><p><?= t_html('project.v6.note_html') ?></p></div>
    </div>
  </div>
</section>

<section class="section section--surface" aria-labelledby="elec-title">
  <div class="container split split--reverse">
    <div class="reveal">
      <h2 id="elec-title"><?= e(t('project.electronics.title')) ?></h2>
      <p><?= e(t('project.electronics.text')) ?></p>
    </div>
    <figure class="split__media reveal d1">
      <?= img_tag('bench-build', t('img.bench-build'), '(min-width: 48em) 50vw, 100vw') ?>
    </figure>
  </div>
</section>

<?php if ($timeline): ?>
<section class="section" aria-labelledby="ptl-title">
  <div class="container">
    <div class="section__head"><h2 id="ptl-title"><?= e(t('project.timeline.title')) ?></h2></div>
    <?= View::partial('partials/timeline', ['items' => $timeline, 'horizontal' => count($timeline) <= 5]) ?>
  </div>
</section>
<?php endif; ?>

<section class="section section--surface" aria-labelledby="gal-title">
  <div class="container">
    <div class="section__head">
      <h2 id="gal-title"><?= e(t('project.gallery.title')) ?></h2>
      <p class="section__lead"><?= e(t('project.gallery.lead')) ?></p>
    </div>
    <ul class="gallery" data-lightbox-root data-lb-label="<?= e(t('project.gallery.title')) ?>" data-lb-close="<?= e(t('common.close')) ?>" data-lb-prev="<?= e(t('common.prev')) ?>" data-lb-next="<?= e(t('common.next')) ?>">
<?php foreach ($gallery as $slug): ?>
      <li>
        <a class="gallery__link" href="<?= e(ui_img_full($slug)) ?>" data-lightbox data-caption="<?= e(t('img.' . $slug)) ?>" title="<?= e(t('common.zoom')) ?>">
          <?= img_tag($slug, t('img.' . $slug), '(min-width: 80em) 25vw, (min-width: 48em) 33vw, 50vw') ?>
        </a>
      </li>
<?php endforeach; ?>
    </ul>
  </div>
</section>

<section class="section section--tight" aria-labelledby="pcta-title">
  <div class="container">
    <div class="cta on-dark reveal">
      <h2 id="pcta-title"><?= e(t('home.cta.title')) ?></h2>
      <p><?= e(t('home.cta.text')) ?></p>
      <div class="btn-row"><a class="btn btn--primary" href="<?= e(route('join')) ?>"><?= e(t('home.cta.button')) ?><?= ui_icon('arrow') ?></a></div>
    </div>
  </div>
</section>
