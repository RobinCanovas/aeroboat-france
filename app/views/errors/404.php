<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/_helpers.php';
?>
<section class="container error-page">
  <p class="error-page__code" aria-hidden="true">404</p>
  <h1><?= e(t('errors.404.title')) ?></h1>
  <p><?= e(t('errors.404.text')) ?></p>
  <p><a class="btn btn--primary" href="<?= e(route('home')) ?>"><?= e(t('errors.404.cta')) ?><?= ui_icon('arrow') ?></a></p>
</section>
