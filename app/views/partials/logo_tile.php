<?php
declare(strict_types=1);

require_once dirname(__DIR__) . "/_helpers.php";

/**
 * Tuile logo d'un partenaire. Variable : $partner (ligne de la table partners).
 * Le logo n'est affiché que si logo_consent = 1 ; sinon le nom seul.
 */
$name = (string) ($partner['name'] ?? '');
$logo = (string) ($partner['logo'] ?? '');
$showLogo = $logo !== '' && !empty($partner['logo_consent']);
$link = (string) ($partner['url'] ?? '');
if ($link !== '' && !preg_match('#^https?://#i', $link)) {
    $link = '';
}
$inner = '';
if ($showLogo) {
    $inner = ui_is_slug($logo)
        ? img_tag($logo, $name, '200px', ['loading' => 'lazy'])
        : '<img src="' . e(ui_upload($logo)) . '" alt="' . e($name) . '" width="200" height="88" loading="lazy" decoding="async">';
} else {
    $inner = '<span class="logo-tile__name">' . e($name) . '</span>';
}
$desc = loc($partner, 'description');
?>
<li class="logo-tile">
<?php if ($link !== ''): ?>
  <a href="<?= e($link) ?>" rel="noopener noreferrer" target="_blank"><?= $inner ?><span class="sr-only"><?= e(t('partners.visit')) ?></span></a>
<?php else: ?>
  <?= $inner ?>
<?php endif; ?>
<?php if (!empty($withDescription) && $desc !== ''): ?>
  <p class="logo-tile__desc"><?= e($desc) ?></p>
<?php endif; ?>
</li>
