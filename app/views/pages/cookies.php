<?php
declare(strict_types=1);

/** Page cookies : vue mince, textes via t(). */
?>
<article class="legal-page container">
    <header class="page-hero">
        <h1><?= e(t('cookies.title')) ?></h1>
        <p class="lead"><?= e(t('cookies.lead')) ?></p>
    </header>

    <section class="prose" aria-labelledby="cookies-visitors">
        <h2 id="cookies-visitors"><?= e(t('cookies.visitors.title')) ?></h2>
        <p><?= e(t('cookies.visitors.text')) ?></p>
    </section>

    <section class="prose" aria-labelledby="cookies-members">
        <h2 id="cookies-members"><?= e(t('cookies.members.title')) ?></h2>
        <p><?= e(t('cookies.members.text')) ?></p>
        <dl class="legal-list">
            <div class="legal-row"><dt><?= e(t('cookies.members.name_label')) ?></dt><dd><code><?= e(t('cookies.members.name_value')) ?></code></dd></div>
            <div class="legal-row"><dt><?= e(t('cookies.members.purpose_label')) ?></dt><dd><?= e(t('cookies.members.purpose_value')) ?></dd></div>
            <div class="legal-row"><dt><?= e(t('cookies.members.duration_label')) ?></dt><dd><?= e(t('cookies.members.duration_value')) ?></dd></div>
        </dl>
    </section>

    <section class="prose" aria-labelledby="cookies-banner">
        <h2 id="cookies-banner"><?= e(t('cookies.banner.title')) ?></h2>
        <p><?= e(t('cookies.banner.text')) ?></p>
    </section>

    <section class="prose" aria-labelledby="cookies-more">
        <h2 id="cookies-more"><?= e(t('cookies.more.title')) ?></h2>
        <p><?= e(t('cookies.more.text')) ?></p>
        <p><a href="<?= e(route('privacy')) ?>"><?= e(t('cookies.more.privacy_link')) ?></a></p>
    </section>
</article>
