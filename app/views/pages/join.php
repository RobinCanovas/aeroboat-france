<?php
declare(strict_types=1);

use App\Core\View;

require_once dirname(__DIR__) . '/_helpers.php';

/** Candidature. Variables : $errors (champ => clé de langue), $sent (bool). Valeurs préremplies via old(). */
$errors = is_array($errors ?? null) ? $errors : [];
$sent = !empty($sent);
$fields = ['first_name', 'last_name', 'email', 'school', 'level', 'skills', 'motivation', 'availability', 'cv'];
$labels = [];
foreach ($fields as $f) {
    $labels[$f] = t('join.fields.' . $f);
}
$labels['consent'] = t('join.consent_short');
$steps = ui_list('join.steps', ['title', 'text'], 5);
?>
<?= View::partial('partials/page_hero', [
    'title' => t('join.hero.title'),
    'lead' => t('join.hero.lead'),
]) ?>

<section class="section" aria-label="<?= e(t('join.hero.title')) ?>">
  <div class="container form-wrap">
<?php if ($sent): ?>
    <div class="form-success" role="status" tabindex="-1" data-form-sent>
      <h2><?= e(t('join.sent.title')) ?></h2>
      <p><?= e(t('join.sent.text')) ?></p>
    </div>
<?php else: ?>
<?php if ($steps): ?>
    <ol class="steps">
<?php foreach ($steps as $s): ?>
      <li><strong><?= e($s['title']) ?></strong><p><?= e($s['text']) ?></p></li>
<?php endforeach; ?>
    </ol>
<?php endif; ?>
    <?= View::partial('partials/error_summary', ['errors' => $errors, 'labels' => $labels]) ?>
    <form class="form" method="post" action="<?= e(route('join')) ?>" enctype="multipart/form-data">
      <?= csrf_field('join') ?>
      <p class="form-legend"><?= e(t('forms.required_legend')) ?></p>
      <div class="form-grid form-grid--2">
        <?= View::partial('partials/field', ['name' => 'first_name', 'label' => $labels['first_name'], 'required' => true, 'errors' => $errors, 'maxlength' => 100, 'autocomplete' => 'given-name']) ?>
        <?= View::partial('partials/field', ['name' => 'last_name', 'label' => $labels['last_name'], 'required' => true, 'errors' => $errors, 'maxlength' => 100, 'autocomplete' => 'family-name']) ?>
      </div>
      <?= View::partial('partials/field', ['name' => 'email', 'type' => 'email', 'label' => $labels['email'], 'required' => true, 'errors' => $errors, 'maxlength' => 190, 'autocomplete' => 'email']) ?>
      <div class="form-grid form-grid--2">
        <?= View::partial('partials/field', ['name' => 'school', 'label' => $labels['school'], 'errors' => $errors, 'maxlength' => 160]) ?>
        <?= View::partial('partials/field', ['name' => 'level', 'label' => $labels['level'], 'errors' => $errors, 'maxlength' => 100]) ?>
      </div>
      <?= View::partial('partials/field', ['name' => 'skills', 'type' => 'textarea', 'rows' => 4, 'label' => $labels['skills'], 'errors' => $errors, 'maxlength' => 2000]) ?>
      <?= View::partial('partials/field', ['name' => 'motivation', 'type' => 'textarea', 'rows' => 6, 'label' => $labels['motivation'], 'required' => true, 'errors' => $errors, 'maxlength' => 5000, 'hint' => t('join.hints.motivation')]) ?>
      <?= View::partial('partials/field', ['name' => 'availability', 'label' => $labels['availability'], 'errors' => $errors, 'maxlength' => 250]) ?>
      <?= View::partial('partials/field', ['name' => 'cv', 'type' => 'file', 'accept' => 'application/pdf,.pdf', 'label' => $labels['cv'], 'errors' => $errors, 'hint' => t('join.hints.cv')]) ?>
      <?= View::partial('partials/consent', ['labelHtml' => t_html('join.consent_html', ['privacy_url' => route('privacy')]), 'errors' => $errors]) ?>
      <div><button class="btn btn--primary" type="submit"><?= e(t('join.submit')) ?></button></div>
    </form>
<?php endif; ?>
  </div>
</section>
