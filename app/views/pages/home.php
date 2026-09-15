<?php
declare(strict_types=1);

use App\Core\Config;

require_once dirname(__DIR__) . '/_helpers.php';

/** Accueil, construit comme le site actuel : héros photo, À propos, membres fondateurs, contact. Variable : $founders. */
$founders = is_array($founders ?? null) ? $founders : [];
$email = (string) Config::env('ORG_EMAIL', '');
$phone = (string) Config::env('ORG_PHONE', '');
$city = (string) Config::env('ORG_CITY', '');
?>
<section class="hero hero--home on-dark" aria-labelledby="hero-title">
  <div class="hero__bg">
    <?= img_tag('hero-v6', t('img.hero-v6'), '100vw', ['loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'async']) ?>
  </div>
  <div class="container hero__inner hero__inner--center">
    <img class="hero__logo" src="<?= e(asset('img/logo-white-512.webp')) ?>" alt="" width="512" height="512">
    <h1 id="hero-title"><?= e(t('home.hero.title')) ?></h1>
    <p class="hero__lead"><?= e(t('home.hero.lead')) ?></p>
    <div class="btn-row btn-row--center">
      <a class="btn btn--primary" href="<?= e(route('join')) ?>"><?= e(t('home.hero.cta_primary')) ?></a>
      <a class="btn btn--ghost" href="<?= e(route('contact')) ?>"><?= e(t('home.hero.cta_secondary')) ?></a>
    </div>
  </div>
</section>

<section class="section" aria-labelledby="about-title">
  <div class="container split">
    <div class="reveal">
      <h2 id="about-title" class="title-bar"><?= e(t('home.about.title')) ?></h2>
      <p class="about__kicker"><?= e(t('home.about.kicker')) ?></p>
      <p class="about__text"><?= e(t('home.about.text')) ?></p>
    </div>
    <figure class="split__media split__media--soft reveal d1">
      <?= img_tag('team-build', t('img.team-build'), '(min-width: 48em) 50vw, 100vw') ?>
    </figure>
  </div>
</section>

<?php if ($founders): ?>
<section class="section section--grey" aria-labelledby="founders-title">
  <div class="container founders">
    <h2 id="founders-title" class="title-bar title-bar--center"><?= e(t('home.founders.title')) ?></h2>
    <p class="founders__text reveal"><?= e(t('home.founders.text')) ?></p>
    <ul class="founders__list">
<?php foreach ($founders as $m):
    $photo = (string) ($m['photo'] ?? '');
    $role = trim(loc($m, 'role')) !== '' ? loc($m, 'role') : t('team.role_default');
    $name = (string) $m['name'];
?>
      <li class="founder reveal">
<?php if ($photo !== '' && !empty($m['photo_consent'])): ?>
        <span class="founder__photo">
<?php if (ui_is_slug($photo)): ?>
          <?= img_tag($photo, '', '160px') ?>
<?php else: ?>
          <img src="<?= e(ui_upload($photo)) ?>" alt="" width="160" height="160" loading="lazy" decoding="async">
<?php endif; ?>
        </span>
<?php endif; ?>
        <span class="founder__name"><?= e($name) ?></span>
        <span class="founder__role"><?= e($role) ?></span>
      </li>
<?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<section class="band-dark" aria-labelledby="contact-title">
  <div class="container">
    <div class="contact-card reveal">
      <div class="contact-card__info">
        <h2 id="contact-title" class="title-bar"><?= e(t('home.contact.title')) ?></h2>
        <p><?= e(t('home.contact.text')) ?></p>
        <ul class="contact-card__list">
<?php if ($phone !== ''): ?>
          <li><span class="sr-only"><?= e(t('contact.info.phone_label')) ?> : </span><a href="tel:<?= e(preg_replace('/[^+0-9]/', '', $phone) ?? '') ?>"><?= e($phone) ?></a></li>
<?php endif; ?>
<?php if ($email !== ''): ?>
          <li><span class="sr-only"><?= e(t('contact.info.email_label')) ?> : </span><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></li>
<?php endif; ?>
<?php if ($city !== ''): ?>
          <li><span class="sr-only"><?= e(t('contact.info.city_label')) ?> : </span><?= e($city) ?></li>
<?php endif; ?>
        </ul>
      </div>
      <div class="contact-card__action">
        <a class="btn btn--primary" href="<?= e(route('contact')) ?>"><?= e(t('home.contact.button')) ?></a>
      </div>
    </div>
  </div>
</section>
