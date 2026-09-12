<?php
declare(strict_types=1);

/**
 * Politique de confidentialité. Vue mince : textes via t() / t_html(), coordonnées via $org (valeurs vides masquées).
 *
 * @var array<string,mixed> $org
 */

$org = isset($org) && is_array($org) ? $org : [];
$o = static function (string $k) use ($org): string {
    foreach ([$k, 'ORG_' . strtoupper($k), 'org_' . $k] as $key) {
        if (isset($org[$key]) && trim((string) $org[$key]) !== '') {
            return trim((string) $org[$key]);
        }
    }
    return '';
};
// Nom de l’hébergeur (ORG_HOST_NAME) ; repli générique si vide, jamais de nom en dur.
$hostLabel = $o('host_name') !== '' ? $o('host_name') : t('privacy.hosting.generic_host');
?>
<article class="legal-page container">
    <header class="page-hero">
        <h1><?= e(t('privacy.title')) ?></h1>
        <p class="lead"><?= e(t('privacy.lead')) ?></p>
    </header>

    <section class="prose" aria-labelledby="privacy-controller">
        <h2 id="privacy-controller"><?= e(t('privacy.controller.title')) ?></h2>
        <p><?= e(t('privacy.controller.text')) ?></p>
        <dl class="legal-list">
            <?php if ($o('name') !== ''): ?>
                <div class="legal-row"><dt><?= e(t('legal.publisher.name_label')) ?></dt><dd><?= e($o('name')) ?></dd></div>
            <?php endif; ?>
            <?php if ($o('address') !== ''): ?>
                <div class="legal-row"><dt><?= e(t('privacy.controller.address_label')) ?></dt><dd><?= e($o('address')) ?></dd></div>
            <?php endif; ?>
            <?php if ($o('email') !== ''): ?>
                <div class="legal-row"><dt><?= e(t('privacy.controller.email_label')) ?></dt><dd><a href="mailto:<?= e($o('email')) ?>"><?= e($o('email')) ?></a></dd></div>
            <?php endif; ?>
            <?php if ($o('phone') !== ''): ?>
                <div class="legal-row"><dt><?= e(t('privacy.controller.phone_label')) ?></dt><dd><?= e($o('phone')) ?></dd></div>
            <?php endif; ?>
        </dl>
    </section>

    <section class="prose" aria-labelledby="privacy-forms">
        <h2 id="privacy-forms"><?= e(t('privacy.forms.title')) ?></h2>
        <p><?= e(t('privacy.forms.intro')) ?></p>
        <h3><?= e(t('privacy.forms.join.title')) ?></h3>
        <p><?= e(t('privacy.forms.join.text')) ?></p>
        <h3><?= e(t('privacy.forms.contact.title')) ?></h3>
        <p><?= e(t('privacy.forms.contact.text')) ?></p>
        <h3><?= e(t('privacy.forms.rights.title')) ?></h3>
        <p><?= e(t('privacy.forms.rights.text')) ?></p>
        <h3><?= e(t('privacy.forms.members.title')) ?></h3>
        <p><?= e(t('privacy.forms.members.text')) ?></p>
        <h3><?= e(t('privacy.forms.security.title')) ?></h3>
        <p><?= e(t('privacy.forms.security.text')) ?></p>
    </section>

    <section class="prose" aria-labelledby="privacy-basis">
        <h2 id="privacy-basis"><?= e(t('privacy.basis.title')) ?></h2>
        <p><?= e(t('privacy.basis.text')) ?></p>
    </section>

    <section class="prose" aria-labelledby="privacy-retention">
        <h2 id="privacy-retention"><?= e(t('privacy.retention.title')) ?></h2>
        <ul>
            <li><?= e(t('privacy.retention.messages')) ?></li>
            <li><?= e(t('privacy.retention.applications')) ?></li>
            <li><?= e(t('privacy.retention.cv')) ?></li>
            <li><?= e(t('privacy.retention.throttle')) ?></li>
            <li><?= e(t('privacy.retention.logs')) ?></li>
        </ul>
        <p><?= e(t('privacy.retention.auto')) ?></p>
        <p><?= e(t('privacy.retention.after')) ?></p>
    </section>

    <section class="prose" aria-labelledby="privacy-recipients">
        <h2 id="privacy-recipients"><?= e(t('privacy.recipients.title')) ?></h2>
        <p><?= e(t('privacy.recipients.text', ['host' => $hostLabel])) ?></p>
    </section>

    <section class="prose" aria-labelledby="privacy-hosting">
        <h2 id="privacy-hosting"><?= e(t('privacy.hosting.title')) ?></h2>
        <p><?= e(t('privacy.hosting.text', ['host' => $hostLabel])) ?></p>
    </section>

    <section class="prose" aria-labelledby="privacy-tracking">
        <h2 id="privacy-tracking"><?= e(t('privacy.tracking.title')) ?></h2>
        <p><?= e(t('privacy.tracking.text')) ?></p>
        <p><a href="<?= e(route('cookies')) ?>"><?= e(t('privacy.tracking.cookies_link')) ?></a></p>
    </section>

    <section class="prose" aria-labelledby="privacy-rights">
        <h2 id="privacy-rights"><?= e(t('privacy.rights.title')) ?></h2>
        <p><?= t_html('privacy.rights.text_html') ?></p>
        <p><a href="<?= e(route('rights')) ?>"><?= e(t('privacy.rights.cta')) ?></a></p>
    </section>

    <section class="prose" aria-labelledby="privacy-complaint">
        <h2 id="privacy-complaint"><?= e(t('privacy.complaint.title')) ?></h2>
        <p><?= t_html('privacy.complaint.text_html') ?></p>
    </section>

    <section class="prose" aria-labelledby="privacy-contact">
        <h2 id="privacy-contact"><?= e(t('privacy.contact.title')) ?></h2>
        <p><?= e(t('privacy.contact.text')) ?></p>
        <?php if ($o('email') !== ''): ?>
            <p><a href="mailto:<?= e($o('email')) ?>"><?= e($o('email')) ?></a></p>
        <?php endif; ?>
    </section>
</article>
