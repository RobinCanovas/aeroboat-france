<?php
declare(strict_types=1);

use App\Core\View;

require_once dirname(__DIR__) . '/_helpers.php';

/** Accueil. Variables : $posts (3 derniers articles publics), $partners (partenaires publiés). */
$posts = is_array($posts ?? null) ? $posts : [];
$partners = is_array($partners ?? null) ? $partners : [];

$stats = ui_list('home.stats', ['value', 'label'], 4);
$cards = ui_list('home.cards', ['title', 'text'], 4);
$timeline = ui_list('home.timeline.items', ['date', 'title', 'text', 'status'], 6);
$cardsClass = count($cards) >= 4 ? 'cards--4' : 'cards--3';
$logoPartners = array_values(array_filter($partners, static fn ($p) => !empty($p['logo']) && !empty($p['logo_consent'])));
?>
<section class="hero on-dark" aria-labelledby="hero-title">
  <div class="hero__bg">
    <?= img_tag('water-skim', t('img.water-skim'), '100vw', ['loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'async']) ?>
  </div>
  <div class="container hero__inner">
    <p class="badge"><span class="badge__dot" aria-hidden="true"></span><?= e(t('home.hero.badge')) ?></p>
    <h1 id="hero-title"><?= e(t('home.hero.title')) ?></h1>
    <p class="hero__lead"><?= e(t('home.hero.lead')) ?></p>
    <div class="btn-row">
      <a class="btn btn--primary" href="<?= e(route('project')) ?>"><?= e(t('home.hero.cta_primary')) ?><?= ui_icon('arrow') ?></a>
      <a class="btn btn--ghost" href="<?= e(route('join')) ?>"><?= e(t('home.hero.cta_secondary')) ?></a>
    </div>
  </div>
<?php if ($stats): ?>
  <div class="container hero__stats">
    <ul class="stats">
<?php foreach ($stats as $s): ?>
      <li class="stats__item"><span class="stats__value"><?= e($s['value']) ?></span><span class="stats__label"><?= e($s['label']) ?></span></li>
<?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>
</section>

<section class="section" aria-labelledby="about-title">
  <div class="container split">
    <div class="reveal">
      <p class="kicker"><?= e(t('home.about.kicker')) ?></p>
      <h2 id="about-title"><?= e(t('home.about.title')) ?></h2>
      <p class="lead-block"><?= e(t('home.about.text')) ?></p>
      <p><a class="btn btn--outline" href="<?= e(route('project')) ?>"><?= e(t('common.read_more')) ?><?= ui_icon('arrow') ?></a></p>
    </div>
    <figure class="split__media reveal d1">
      <?= img_tag('lakeside-duo', t('img.lakeside-duo'), '(min-width: 48em) 50vw, 100vw') ?>
    </figure>
  </div>
</section>

<?php if ($cards): ?>
<section class="section section--surface" aria-label="<?= e(t('home.about.title')) ?>">
  <div class="container">
    <ol class="cards <?= $cardsClass ?>">
<?php foreach ($cards as $i => $c): ?>
      <li class="card reveal d<?= $i % 4 ?>">
        <span class="card__num" aria-hidden="true"><?= sprintf('%02d', $i + 1) ?></span>
        <h3><?= e($c['title']) ?></h3>
        <p><?= e($c['text']) ?></p>
      </li>
<?php endforeach; ?>
    </ol>
  </div>
</section>
<?php endif; ?>

<?php if ($timeline): ?>
<section class="section" aria-labelledby="tl-title">
  <div class="container">
    <div class="section__head">
      <h2 id="tl-title"><?= e(t('home.timeline.title')) ?></h2>
      <p class="section__lead"><?= e(t('home.timeline.lead')) ?></p>
    </div>
    <?= View::partial('partials/timeline', ['items' => $timeline, 'horizontal' => true]) ?>
  </div>
</section>
<?php endif; ?>

<section class="section section--surface" aria-labelledby="partners-title">
  <div class="container">
    <div class="section__head">
      <h2 id="partners-title"><?= e(t('home.partners.title')) ?></h2>
      <p class="section__lead"><?= e(t('home.partners.lead')) ?></p>
    </div>
<?php if ($logoPartners): ?>
    <ul class="logo-wall">
<?php foreach ($logoPartners as $p): ?>
      <?= View::partial('partials/logo_tile', ['partner' => $p]) ?>
<?php endforeach; ?>
    </ul>
<?php endif; ?>
    <p class="btn-row"><a class="btn btn--outline" href="<?= e(route('partners')) ?>"><?= e(t('home.partners.cta')) ?><?= ui_icon('arrow') ?></a></p>
  </div>
</section>

<section class="section" aria-labelledby="news-title">
  <div class="container">
    <div class="section__head">
      <h2 id="news-title"><?= e(t('home.news.title')) ?></h2>
    </div>
<?php if ($posts): ?>
    <ul class="posts posts--3">
<?php foreach ($posts as $post): ?>
      <li><?= View::partial('partials/post_card', ['post' => $post]) ?></li>
<?php endforeach; ?>
    </ul>
<?php else: ?>
    <p class="empty-note"><?= e(t('home.news.empty')) ?></p>
<?php endif; ?>
    <p class="btn-row"><a class="btn btn--outline" href="<?= e(route('news')) ?>"><?= e(t('home.news.cta')) ?><?= ui_icon('arrow') ?></a></p>
  </div>
</section>

<section class="section section--tight" aria-labelledby="cta-title">
  <div class="container">
    <div class="cta on-dark reveal">
      <h2 id="cta-title"><?= e(t('home.cta.title')) ?></h2>
      <p><?= e(t('home.cta.text')) ?></p>
      <div class="btn-row"><a class="btn btn--primary" href="<?= e(route('join')) ?>"><?= e(t('home.cta.button')) ?><?= ui_icon('arrow') ?></a></div>
    </div>
  </div>
</section>
