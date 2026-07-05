<?php
declare(strict_types=1);

/**
 * Mentions légales. Vue mince : textes via t(), identité via $org (variables ORG_*).
 * Une valeur vide n’est jamais affichée (ni libellé, ni ligne) : aucun faux numéro.
 *
 * @var array<string,mixed> $org
 */

$org = isset($org) && is_array($org) ? $org : [];
// Accepte les clés « name », « ORG_NAME » ou « org_name ».
$o = static function (string $k) use ($org): string {
    foreach ([$k, 'ORG_' . strtoupper($k), 'org_' . $k] as $key) {
        if (isset($org[$key]) && trim((string) $org[$key]) !== '') {
            return trim((string) $org[$key]);
        }
    }
    return '';
};

$publisherRows = [
    ['legal.publisher.name_label', $o('name')],
    ['legal.publisher.legal_form_label', $o('legal_form')],
    ['legal.publisher.rna_label', $o('rna')],
    ['legal.publisher.address_label', $o('address')],
    ['legal.publisher.city_label', $o('city')],
    ['legal.publisher.email_label', $o('email')],
    ['legal.publisher.phone_label', $o('phone')],
    ['legal.publisher.director_label', $o('publisher')],
];
$hostRows = [
    ['legal.host.name_label', $o('host_name')],
    ['legal.host.address_label', $o('host_address')],
];
?>
<article class="legal-page container">
    <header class="page-hero">
        <h1><?= e(t('legal.title')) ?></h1>
        <p class="lead"><?= e(t('legal.lead')) ?></p>
    </header>

    <section class="prose" aria-labelledby="legal-publisher">
        <h2 id="legal-publisher"><?= e(t('legal.publisher.title')) ?></h2>
        <dl class="legal-list">
            <?php foreach ($publisherRows as [$labelKey, $value]): ?>
                <?php if ($value === '') { continue; } ?>
                <div class="legal-row">
                    <dt><?= e(t($labelKey)) ?></dt>
                    <dd><?php if ($labelKey === 'legal.publisher.email_label'): ?><a href="mailto:<?= e($value) ?>"><?= e($value) ?></a><?php else: ?><?= e($value) ?><?php endif; ?></dd>
                </div>
            <?php endforeach; ?>
        </dl>
    </section>

    <?php if ($o('host_name') !== '' || $o('host_address') !== ''): ?>
        <section class="prose" aria-labelledby="legal-host">
            <h2 id="legal-host"><?= e(t('legal.host.title')) ?></h2>
            <dl class="legal-list">
                <?php foreach ($hostRows as [$labelKey, $value]): ?>
                    <?php if ($value === '') { continue; } ?>
                    <div class="legal-row">
                        <dt><?= e(t($labelKey)) ?></dt>
                        <dd><?= e($value) ?></dd>
                    </div>
                <?php endforeach; ?>
            </dl>
        </section>
    <?php endif; ?>

    <section class="prose" aria-labelledby="legal-ip">
        <h2 id="legal-ip"><?= e(t('legal.ip.title')) ?></h2>
        <p><?= e(t('legal.ip.text')) ?></p>
    </section>

    <section class="prose" aria-labelledby="legal-liability">
        <h2 id="legal-liability"><?= e(t('legal.liability.title')) ?></h2>
        <p><?= e(t('legal.liability.text')) ?></p>
    </section>

    <section class="prose" aria-labelledby="legal-data">
        <h2 id="legal-data"><?= e(t('legal.data.title')) ?></h2>
        <p><?= e(t('legal.data.text')) ?></p>
        <ul>
            <li><a href="<?= e(route('privacy')) ?>"><?= e(t('legal.data.privacy_link')) ?></a></li>
            <li><a href="<?= e(route('rights')) ?>"><?= e(t('legal.data.rights_link')) ?></a></li>
        </ul>
    </section>
</article>
