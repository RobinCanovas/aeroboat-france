<?php
declare(strict_types=1);

use App\Core\View;

require_once dirname(__DIR__) . '/_helpers.php';

/** Liste d'actualités. Variables : $posts, $page (numéro de page courant), $pages (nombre de pages). */
$posts = is_array($posts ?? null) ? $posts : [];
$current = max(1, is_numeric($page ?? null) ? (int) $page : (int) ($_GET['page'] ?? 1));
$total = max(1, (int) ($pages ?? 1));
$base = route('news');
?>
<?= View::partial('partials/page_hero', [
    'title' => t('news.hero.title'),
    'lead' => t('news.hero.lead'),
]) ?>

<section class="section" aria-label="<?= e(t('news.hero.title')) ?>">
  <div class="container">
<?php if ($posts): ?>
    <ul class="posts">
<?php foreach ($posts as $post): ?>
      <li class="reveal"><?= View::partial('partials/post_card', ['post' => $post]) ?></li>
<?php endforeach; ?>
    </ul>
<?php if ($total > 1): ?>
    <nav class="pagination" aria-label="<?= e(t('news.page_of', ['page' => $current, 'pages' => $total])) ?>">
<?php if ($current > 1): ?>
      <a class="btn btn--outline" href="<?= e($base . ($current - 1 > 1 ? '?page=' . ($current - 1) : '')) ?>" rel="prev">&larr; <?= e(t('common.prev')) ?></a>
<?php endif; ?>
      <span class="pagination__info" aria-current="page"><?= e(t('news.page_of', ['page' => $current, 'pages' => $total])) ?></span>
<?php if ($current < $total): ?>
      <a class="btn btn--outline" href="<?= e($base . '?page=' . ($current + 1)) ?>" rel="next"><?= e(t('common.next')) ?> &rarr;</a>
<?php endif; ?>
    </nav>
<?php endif; ?>
<?php else: ?>
    <p class="empty-note"><?= e(t('news.empty')) ?></p>
<?php endif; ?>
  </div>
</section>
