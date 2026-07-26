<?php
declare(strict_types=1);

use App\Core\View;

require_once dirname(__DIR__) . '/_helpers.php';

/** Équipe. Variable : $members (membres publiés, triés). */
$members = is_array($members ?? null) ? $members : [];
?>
<?= View::partial('partials/page_hero', [
    'title' => t('team.hero.title'),
    'lead' => t('team.hero.lead'),
]) ?>

<section class="section" aria-labelledby="who-title">
  <div class="container split">
    <div class="reveal">
      <h2 id="who-title"><?= e(t('team.who.title')) ?></h2>
      <p><?= e(t('team.who.text')) ?></p>
      <p><a class="btn btn--primary" href="<?= e(route('join')) ?>"><?= e(t('nav.join')) ?><?= ui_icon('arrow') ?></a></p>
    </div>
    <figure class="split__media reveal d1">
      <?= img_tag('event-stand', t('img.event-stand'), '(min-width: 48em) 50vw, 100vw') ?>
    </figure>
  </div>
</section>

<?php if ($members): ?>
<section class="section section--surface" aria-labelledby="members-title">
  <div class="container">
    <h2 id="members-title" class="sr-only"><?= e(t('nav.team')) ?></h2>
    <ul class="team-grid">
<?php foreach ($members as $m): ?>
<?php
    $name = (string) ($m['name'] ?? '');
    $photo = (string) ($m['photo'] ?? '');
    $hasPhoto = $photo !== '' && !empty($m['photo_consent']);
    $role = loc($m, 'role');
    $bio = loc($m, 'bio');
    $linkedin = (string) ($m['linkedin'] ?? '');
    if (!preg_match('#^https://#i', $linkedin)) {
        $linkedin = '';
    }
?>
      <li>
        <article class="member reveal">
          <div class="member__photo">
<?php if ($hasPhoto && ui_is_slug($photo)): ?>
            <?= img_tag($photo, '', '(min-width: 80em) 18rem, (min-width: 48em) 30vw, 46vw') ?>
<?php elseif ($hasPhoto): ?>
            <img src="<?= e(ui_upload($photo)) ?>" alt="" width="640" height="800" loading="lazy" decoding="async">
<?php else: ?>
            <span class="member__initials" aria-hidden="true"><?= e(ui_initials($name)) ?></span>
<?php endif; ?>
          </div>
          <div class="member__body">
            <h3 class="member__name"><?= e($name) ?></h3>
            <p class="member__role"><?= e($role !== '' ? $role : t('team.role_default')) ?></p>
<?php if ($bio !== ''): ?>
            <p class="member__bio"><?= e($bio) ?></p>
<?php endif; ?>
<?php if ($linkedin !== ''): ?>
            <a class="member__link" href="<?= e($linkedin) ?>" rel="noopener noreferrer" target="_blank"><?= e(t('footer.social.linkedin')) ?><span class="sr-only"> : <?= e($name) ?></span></a>
<?php endif; ?>
          </div>
        </article>
      </li>
<?php endforeach; ?>
    </ul>
    <p class="photo-note"><?= e(t('team.photo_note')) ?></p>
  </div>
</section>
<?php endif; ?>
