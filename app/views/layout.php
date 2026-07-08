<?php
declare(strict_types=1);

/**
 * Gabarit commun. Variables : $content (HTML de la vue, déjà rendu), $meta, $routeKey.
 * Aucun style en ligne, aucun gestionnaire en ligne, aucun script en ligne.
 */
require_once __DIR__ . '/_helpers.php';

$lang = lang();
$meta = is_array($meta ?? null) ? $meta : [];
// $routeKey = clé de route courante (nav active, classe de page). $page n'est un numéro que sur la liste d'actualités.
$page = is_string($routeKey ?? null) ? $routeKey : (is_string($page ?? null) && !is_numeric($page) ? $page : '');
$alternates = is_array($meta['alternates'] ?? null) ? $meta['alternates'] : [];

$siteName = t('meta.site_name');
$title = (string) ($meta['title'] ?? $siteName);
if (!str_contains($title, $siteName)) {
    $title .= t('meta.title_suffix');
}
$description = (string) ($meta['description'] ?? '') !== '' ? (string) $meta['description'] : t('meta.default_description');
$canonical = (string) ($meta['canonical'] ?? '');
$robots = (string) ($meta['robots'] ?? '') !== '' ? (string) $meta['robots'] : (is_test() ? 'noindex, nofollow' : 'index, follow');

$ogImage = (string) ($meta['image'] ?? '');
if ($ogImage === '') {
    $ogImage = ui_img_full('lake-alpine');
} elseif (!preg_match('#^https?://#', $ogImage)) {
    $ogImage = ui_is_slug($ogImage) ? ui_img_full($ogImage) : ui_upload($ogImage);
}

$navItems = [
    ['home', 'nav.home', false],
    ['project', 'nav.project', false],
    ['team', 'nav.team', false],
    ['partners', 'nav.partners', false],
    ['news', 'nav.news', false],
    ['contact', 'nav.contact', false],
    ['join', 'nav.join', true],
];
$activeKey = $page === 'news_show' ? 'news' : $page;

$social = [
    ['instagram', 'footer.social.instagram', 'https://www.instagram.com/aeroboat_france/'],
    ['linkedin', 'footer.social.linkedin', 'https://www.linkedin.com/company/aeroboat-france/'],
];
?><!DOCTYPE html>
<html lang="<?= e($lang) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($description) ?>">
<meta name="robots" content="<?= e($robots) ?>">
<meta name="theme-color" content="#0a1f44">
<?php if ($canonical !== ''): ?>
<link rel="canonical" href="<?= e($canonical) ?>">
<?php endif; ?>
<?php foreach ($alternates as $altLang => $altUrl): ?>
<link rel="alternate" hreflang="<?= e((string) $altLang) ?>" href="<?= e((string) $altUrl) ?>">
<?php endforeach; ?>
<?php if (isset($alternates['fr'])): ?>
<link rel="alternate" hreflang="x-default" href="<?= e((string) $alternates['fr']) ?>">
<?php endif; ?>
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e($siteName) ?>">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($description) ?>">
<meta property="og:locale" content="<?= $lang === 'fr' ? 'fr_FR' : 'en_GB' ?>">
<?php if ($canonical !== ''): ?>
<meta property="og:url" content="<?= e($canonical) ?>">
<?php endif; ?>
<meta property="og:image" content="<?= e($ogImage) ?>">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="<?= e(asset('favicon.ico')) ?>" sizes="any">
<link rel="icon" type="image/png" sizes="32x32" href="<?= e(asset('img/icon-32.png')) ?>">
<link rel="apple-touch-icon" href="<?= e(asset('img/icon-180.png')) ?>">
<link rel="manifest" href="<?= e(asset('manifest.webmanifest')) ?>">
<?= vite_tags('frontend/main.js') ?>
</head>
<body class="page-<?= e($page !== '' ? $page : 'default') ?>">
<a class="skip-link" href="#main"><?= e(t('nav.skip_to_content')) ?></a>

<?php if (is_test()): ?>
<div class="test-banner" role="note"><?= e(t('meta.test_banner')) ?></div>
<?php endif; ?>

<header class="site-header">
  <div class="container site-header__bar">
    <a class="brand" href="<?= e(route('home')) ?>">
      <?= img_tag('logo', '', '44px', ['class' => 'brand__logo', 'loading' => 'eager']) ?>
      <span><?= e($siteName) ?></span>
    </a>

    <button class="nav-toggle" type="button" hidden data-nav-toggle aria-expanded="false" aria-controls="site-nav" data-label-open="<?= e(t('nav.menu_open')) ?>" data-label-close="<?= e(t('nav.menu_close')) ?>">
      <span class="nav-toggle__bars" aria-hidden="true"></span>
      <span data-nav-label><?= e(t('nav.menu_open')) ?></span>
    </button>

    <nav id="site-nav" class="site-nav" aria-label="<?= e(t('nav.main_label')) ?>">
      <ul class="site-nav__list">
<?php foreach ($navItems as [$key, $labelKey, $isCta]): ?>
        <li><a class="site-nav__link<?= $isCta ? ' site-nav__link--cta' : '' ?>" href="<?= e(route($key)) ?>"<?= $activeKey === $key ? ' aria-current="page"' : '' ?>><?= e(t($labelKey)) ?></a></li>
<?php endforeach; ?>
      </ul>
      <div class="lang-switch" role="group" aria-label="<?= e(t('nav.language_label')) ?>">
<?php foreach (['fr', 'en'] as $code): ?>
<?php $href = (string) ($alternates[$code] ?? route('home', [], $code)); ?>
        <a class="lang-switch__link" href="<?= e($href) ?>" hreflang="<?= $code ?>" lang="<?= $code ?>" aria-label="<?= e(t('nav.lang_' . $code)) ?>"<?= $lang === $code ? ' aria-current="true"' : '' ?>><?= strtoupper($code) ?></a>
<?php endforeach; ?>
      </div>
    </nav>
  </div>
</header>

<main id="main" tabindex="-1">
<?= $content ?? '' ?>
</main>

<footer class="site-footer">
  <div class="container footer-grid">
    <section class="footer-col" aria-labelledby="ft-about">
      <div class="footer-brand">
        <?= img_tag('logo-white', '', '56px', ['class' => 'footer-brand__logo']) ?>
        <span><?= e($siteName) ?></span>
      </div>
      <h2 id="ft-about" class="sr-only"><?= e(t('footer.about.title')) ?></h2>
      <p><?= e(t('footer.about.text')) ?></p>
    </section>

    <nav class="footer-col" aria-labelledby="ft-nav">
      <h2 id="ft-nav"><?= e(t('footer.nav.title')) ?></h2>
      <ul class="footer-list">
<?php foreach ($navItems as [$key, $labelKey, $isCta]): ?>
        <li><a href="<?= e(route($key)) ?>"><?= e(t($labelKey)) ?></a></li>
<?php endforeach; ?>
      </ul>
    </nav>

    <nav class="footer-col" aria-labelledby="ft-legal">
      <h2 id="ft-legal"><?= e(t('footer.legal.title')) ?></h2>
      <ul class="footer-list">
        <li><a href="<?= e(route('legal')) ?>"><?= e(t('footer.legal.legal')) ?></a></li>
        <li><a href="<?= e(route('privacy')) ?>"><?= e(t('footer.legal.privacy')) ?></a></li>
        <li><a href="<?= e(route('cookies')) ?>"><?= e(t('footer.legal.cookies')) ?></a></li>
        <li><a href="<?= e(route('rights')) ?>"><?= e(t('footer.legal.rights')) ?></a></li>
      </ul>
    </nav>

    <section class="footer-col" aria-labelledby="ft-contact">
      <h2 id="ft-contact"><?= e(t('footer.contact.title')) ?></h2>
      <p><?= e(t('footer.contact.text')) ?></p>
      <p><a class="btn btn--light" href="<?= e(route('contact')) ?>"><?= e(t('nav.contact')) ?></a></p>
      <h3 class="sr-only"><?= e(t('footer.social.title')) ?></h3>
      <ul class="footer-social">
<?php foreach ($social as [$icon, $labelKey, $href]): ?>
        <li><a href="<?= e($href) ?>" rel="noopener noreferrer" target="_blank"><?= ui_icon($icon) ?><span><?= e(t($labelKey)) ?></span></a></li>
<?php endforeach; ?>
      </ul>
    </section>
  </div>

  <div class="container footer-bottom">
    <p><?= e(t('footer.copyright', ['year' => gmdate('Y')])) ?></p>
    <p><?= e(t('footer.no_tracking')) ?></p>
  </div>
</footer>
</body>
</html>
