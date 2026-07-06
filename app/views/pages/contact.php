<?php
declare(strict_types=1);

use App\Core\Config;
use App\Core\View;

require_once dirname(__DIR__) . '/_helpers.php';

/** Contact. Variables : $errors, $sent. Coordonnées : ORG_EMAIL / ORG_PHONE / ORG_CITY (vides = masquées). */
$errors = is_array($errors ?? null) ? $errors : [];
$sent = !empty($sent);
$labels = [];
foreach (['name', 'email', 'subject', 'body'] as $f) {
    $labels[$f] = t('contact.fields.' . $f);
}
$labels['consent'] = strip_tags(t_html('contact.consent_html', ['privacy_url' => route('privacy')]));
$orgEmail = trim((string) Config::env('ORG_EMAIL', ''));
$orgPhone = trim((string) Config::env('ORG_PHONE', ''));
$orgCity = trim((string) Config::env('ORG_CITY', ''));
?>
<?= View::partial('partials/page_hero', [
    'title' => t('contact.hero.title'),
    'lead' => t('contact.hero.lead'),
]) ?>

<section class="section" aria-label="<?= e(t('contact.hero.title')) ?>">
  <div class="container contact-layout">
    <div>
<?php if ($sent): ?>
      <div class="form-success" role="status" tabindex="-1" data-form-sent>
        <h2><?= e(t('contact.sent.title')) ?></h2>
        <p><?= e(t('contact.sent.text')) ?></p>
      </div>
<?php else: ?>
      <?= View::partial('partials/error_summary', ['errors' => $errors, 'labels' => $labels]) ?>
      <form class="form" method="post" action="<?= e(route('contact')) ?>">
        <?= csrf_field('contact') ?>
        <div class="form-grid form-grid--2">
          <?= View::partial('partials/field', ['name' => 'name', 'label' => $labels['name'], 'required' => true, 'errors' => $errors, 'maxlength' => 120, 'autocomplete' => 'name']) ?>
          <?= View::partial('partials/field', ['name' => 'email', 'type' => 'email', 'label' => $labels['email'], 'required' => true, 'errors' => $errors, 'maxlength' => 190, 'autocomplete' => 'email']) ?>
        </div>
        <?= View::partial('partials/field', ['name' => 'subject', 'label' => $labels['subject'], 'errors' => $errors, 'maxlength' => 200]) ?>
        <?= View::partial('partials/field', ['name' => 'body', 'type' => 'textarea', 'rows' => 7, 'label' => $labels['body'], 'required' => true, 'errors' => $errors]) ?>
        <?= View::partial('partials/consent', ['labelHtml' => t_html('contact.consent_html', ['privacy_url' => route('privacy')]), 'errors' => $errors]) ?>
        <div><button class="btn btn--primary" type="submit"><?= e(t('contact.submit')) ?></button></div>
      </form>
<?php endif; ?>
    </div>

<?php if ($orgEmail !== '' || $orgPhone !== '' || $orgCity !== ''): ?>
    <aside class="info-card" aria-labelledby="info-title">
      <h2 id="info-title"><?= e(t('contact.info.title')) ?></h2>
      <dl>
<?php if ($orgEmail !== ''): ?>
        <div><dt><?= e(t('contact.info.email_label')) ?></dt><dd><a href="mailto:<?= e($orgEmail) ?>"><?= e($orgEmail) ?></a></dd></div>
<?php endif; ?>
<?php if ($orgPhone !== ''): ?>
        <div><dt><?= e(t('contact.info.phone_label')) ?></dt><dd><a href="tel:<?= e(preg_replace('/[^+\d]/', '', $orgPhone) ?? '') ?>"><?= e($orgPhone) ?></a></dd></div>
<?php endif; ?>
<?php if ($orgCity !== ''): ?>
        <div><dt><?= e(t('contact.info.city_label')) ?></dt><dd><?= e($orgCity) ?></dd></div>
<?php endif; ?>
      </dl>
    </aside>
<?php endif; ?>
  </div>
</section>
