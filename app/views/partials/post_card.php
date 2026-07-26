<?php
declare(strict_types=1);

require_once dirname(__DIR__) . "/_helpers.php";

/** Carte d'article. Variables : $post (ligne de la table posts), $headingLevel (2 ou 3, défaut 3). Toute la carte est cliquable via le titre. */
$title = loc($post, 'title');
$excerpt = loc($post, 'excerpt');
$href = route('news_show', ['slug' => loc($post, 'slug')]);
$date = ui_date((string) ($post['publish_at'] ?? '') !== '' ? (string) $post['publish_at'] : (string) ($post['created_at'] ?? ''));
$cover = (string) ($post['cover'] ?? '');
$coverAlt = loc($post, 'cover_alt');
$hl = in_array((int) ($headingLevel ?? 3), [2, 3], true) ? (int) ($headingLevel ?? 3) : 3;
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
    <h<?= $hl ?>><a href="<?= e($href) ?>"><?= e($title) ?></a></h<?= $hl ?>>
<?php if ($excerpt !== ''): ?>
    <p><?= e($excerpt) ?></p>
<?php endif; ?>
    <span class="post-card__more" aria-hidden="true"><?= e(t('common.read_more')) ?> &rarr;</span>
  </div>
</article>
