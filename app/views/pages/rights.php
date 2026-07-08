<?php
declare(strict_types=1);

use App\Core\View;

require_once dirname(__DIR__) . '/_helpers.php';

/** Exercice des droits RGPD. Variables : $errors, $sent. */
$errors = is_array($errors ?? null) ? $errors : [];
$sent = !empty($sent);
$labels = [];
foreach (['name', 'email', 'request_type', 'body'] as $f) {
    $labels[$f] = t('rights.fields.' . $f);
}
$labels['consent'] = strip_tags(t_html('rights.consent_html', ['privacy_url' => route('privacy')]));
$types = [];
foreach (['access', 'rectify', 'erase', 'object', 'portability'] as $k) {
    $types[$k] = t('rights.types.' . $k);
}
?>
<?= View::partial('partials/page_hero', [
    'title' => t('rights.hero.title'),
    'lead' => t('rights.hero.lead'),
]) ?>

<section class="section" aria-label="<?= e(t('rights.hero.title')) ?>">
  <div class="container form-wrap">
<?php if ($sent): ?>
    <div class="form-success" role="status" tabindex="-1" data-form-sent>
      <h2><?= e(t('rights.sent.title')) ?></h2>
      <p><?= e(t('rights.sent.text')) ?></p>
    </div>
<?php else: ?>
    <?= View::partial('partials/error_summary', ['errors' => $errors, 'labels' => $labels]) ?>
    <form class="form" method="post" action="<?= e(route('rights')) ?>">
      <?= csrf_field('rights') ?>
      <div class="form-grid form-grid--2">
        <?= View::partial('partials/field', ['name' => 'name', 'label' => $labels['name'], 'required' => true, 'errors' => $errors, 'maxlength' => 120, 'autocomplete' => 'name']) ?>
        <?= View::partial('partials/field', ['name' => 'email', 'type' => 'email', 'label' => $labels['email'], 'required' => true, 'errors' => $errors, 'maxlength' => 190, 'autocomplete' => 'email']) ?>
      </div>
      <?= View::partial('partials/field', ['name' => 'request_type', 'type' => 'select', 'options' => $types, 'label' => $labels['request_type'], 'required' => true, 'errors' => $errors]) ?>
      <?= View::partial('partials/field', ['name' => 'body', 'type' => 'textarea', 'rows' => 6, 'label' => $labels['body'], 'required' => true, 'errors' => $errors]) ?>
      <?= View::partial('partials/consent', ['labelHtml' => t_html('rights.consent_html', ['privacy_url' => route('privacy')]), 'errors' => $errors]) ?>
      <p class="note"><?= e(t('rights.delay_note')) ?></p>
      <div><button class="btn btn--primary" type="submit"><?= e(t('rights.submit')) ?></button></div>
    </form>
<?php endif; ?>
  </div>
</section>
