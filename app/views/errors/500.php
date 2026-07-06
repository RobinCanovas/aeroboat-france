<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/_helpers.php';
?>
<section class="container error-page">
  <p class="error-page__code" aria-hidden="true">500</p>
  <h1><?= e(t('errors.500.title')) ?></h1>
  <p><?= e(t('errors.500.text')) ?></p>
  <p><a class="btn btn--primary" href="<?= e(route('home')) ?>"><?= e(t('nav.home')) ?><?= ui_icon('arrow') ?></a></p>
</section>
