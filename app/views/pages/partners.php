<?php
declare(strict_types=1);

use App\Core\View;

require_once dirname(__DIR__) . '/_helpers.php';

/**
 * Partenaires. Variable : $partnersByCategory (catégorie => lignes publiées).
 * IONIS et 3DEXPERIENCE ont leur bloc dédié ; ils sont retirés des listes par catégorie.
 */
$byCategory = is_array($partnersByCategory ?? null) ? $partnersByCategory : [];

$featured = ['ionis' => null, 'dx' => null];
$others = [];
foreach ($byCategory as $cat => $rows) {
    foreach ((array) $rows as $p) {
        $n = (string) ($p['name'] ?? '');
        if (stripos($n, 'ionis') !== false) {
            $featured['ionis'] = $p;
        } elseif (stripos($n, '3dexperience') !== false) {
            $featured['dx'] = $p;
        } else {
            $others[(string) $cat][] = $p;
        }
    }
}
$linkOf = static function (?array $p): string {
    $u = (string) ($p['url'] ?? '');
    return preg_match('#^https?://#i', $u) ? $u : '';
};
$dxFacts = [t('partners.dx.licenses_label'), t('partners.dx.years_label'), t('partners.dx.others_label')];
?>
<?= View::partial('partials/page_hero', [
    'title' => t('partners.hero.title'),
    'lead' => t('partners.hero.lead'),
]) ?>

<section class="section" aria-labelledby="ionis-title">
  <div class="container">
    <article class="feature reveal">
      <div class="feature__logo">
        <?= img_tag('partner-ionis', t('img.partner-ionis'), '224px') ?>
      </div>
      <div>
        <p class="badge-light"><?= e(t('partners.ionis.badge')) ?></p>
        <h2 id="ionis-title"><?= e(t('partners.ionis.title')) ?></h2>
        <p><?= e(t('partners.ionis.text')) ?></p>
<?php if ($linkOf($featured['ionis']) !== ''): ?>
        <p><a href="<?= e($linkOf($featured['ionis'])) ?>" rel="noopener noreferrer" target="_blank"><?= e(t('partners.visit')) ?></a></p>
<?php endif; ?>
      </div>
    </article>

    <article class="feature reveal" aria-labelledby="dx-title">
      <div class="feature__logo">
        <?= img_tag('partner-3dx', t('img.partner-3dx'), '224px') ?>
      </div>
      <div>
        <h2 id="dx-title"><?= e(t('partners.dx.title')) ?></h2>
        <p><?= e(t('partners.dx.text')) ?></p>
        <ul class="feature__facts">
<?php foreach ($dxFacts as $fact): ?>
          <li><?= e($fact) ?></li>
<?php endforeach; ?>
        </ul>
<?php if ($linkOf($featured['dx']) !== ''): ?>
        <p><a href="<?= e($linkOf($featured['dx'])) ?>" rel="noopener noreferrer" target="_blank"><?= e(t('partners.visit')) ?></a></p>
<?php endif; ?>
      </div>
    </article>

<?php foreach ($others as $cat => $rows): ?>
    <section class="category" aria-labelledby="cat-<?= e($cat) ?>">
      <h3 id="cat-<?= e($cat) ?>"><?= e(ui_has('partners.categories.' . $cat) ? t('partners.categories.' . $cat) : $cat) ?></h3>
      <ul class="logo-wall">
<?php foreach ($rows as $p): ?>
        <?= View::partial('partials/logo_tile', ['partner' => $p, 'withDescription' => true]) ?>
<?php endforeach; ?>
      </ul>
    </section>
<?php endforeach; ?>
  </div>
</section>

<section class="section section--tight" aria-labelledby="become-title">
  <div class="container">
    <div class="cta on-dark reveal">
      <h2 id="become-title"><?= e(t('partners.become.title')) ?></h2>
      <p><?= e(t('partners.become.text')) ?></p>
      <div class="btn-row"><a class="btn btn--primary" href="<?= e(route('contact')) ?>"><?= e(t('partners.become.button')) ?><?= ui_icon('arrow') ?></a></div>
    </div>
  </div>
</section>
