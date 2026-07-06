<?php
declare(strict_types=1);

use App\Core\View;

require_once dirname(__DIR__) . '/_helpers.php';

/** Article. Variables : $post (ligne posts), $bodyHtml (HTML issu de Markdown::render, déjà neutralisé). */
$title = loc($post, 'title');
$rawDate = (string) ($post['publish_at'] ?? '') !== '' ? (string) $post['publish_at'] : (string) ($post['created_at'] ?? '');
$date = ui_date($rawDate);
$cover = (string) ($post['cover'] ?? '');
?>
<?= View::partial('partials/page_hero', [
    'title' => $title,
    'back' => ['url' => route('news'), 'label' => t('news.back')],
]) ?>

<article class="section">
  <div class="container article">
<?php if ($date !== ''): ?>
    <p class="post-card__date"><time datetime="<?= e(substr($rawDate, 0, 10)) ?>"><?= e(t('common.published_on', ['date' => $date])) ?></time></p>
<?php endif; ?>
<?php if ($cover !== ''): ?>
    <figure class="article__cover">
      <img src="<?= e(ui_upload($cover)) ?>" alt="<?= e(loc($post, 'cover_alt')) ?>" width="1200" height="675" decoding="async">
    </figure>
<?php endif; ?>
    <div class="prose">
      <?= $bodyHtml ?? '' ?>
    </div>
    <p><a class="btn btn--outline" href="<?= e(route('news')) ?>">&larr; <?= e(t('news.back')) ?></a></p>
  </div>
</article>
