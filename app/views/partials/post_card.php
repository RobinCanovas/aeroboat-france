<?php
declare(strict_types=1);

require_once dirname(__DIR__) . "/_helpers.php";

/** Carte d'article. Variable : $post (ligne de la table posts). Toute la carte est cliquable via le titre. */
$title = loc($post, 'title');
$excerpt = loc($post, 'excerpt');
$href = route('news_show', ['slug' => loc($post, 'slug')]);
$date = ui_date((string) ($post['publish_at'] ?? '') !== '' ? (string) $post['publish_at'] : (string) ($post['created_at'] ?? ''));
$cover = (string) ($post['cover'] ?? '');
$coverAlt = loc($post, 'cover_alt');
?>
<article class="post-card">
<?php if ($cover !== ''): ?>
  <div class="post-card__media">
    <img src="<?= e(ui_upload($cover)) ?>" alt="<?= e($coverAlt) ?>" width="800" height="450" loading="lazy" decoding="async">
  </div>
<?php endif; ?>
  <div class="post-card__body">
<?php if ($date !== ''): ?>
    <p class="post-card__date"><time datetime="<?= e(substr((string) ($post['publish_at'] ?? $post['created_at'] ?? ''), 0, 10)) ?>"><?= e(t('common.published_on', ['date' => $date])) ?></time></p>
<?php endif; ?>
    <h3><a href="<?= e($href) ?>"><?= e($title) ?></a></h3>
<?php if ($excerpt !== ''): ?>
    <p><?= e($excerpt) ?></p>
<?php endif; ?>
    <span class="post-card__more" aria-hidden="true"><?= e(t('common.read_more')) ?> &rarr;</span>
  </div>
</article>
