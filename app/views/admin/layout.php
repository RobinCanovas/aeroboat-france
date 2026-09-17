<?php
declare(strict_types=1);

use App\Admin\Ui;

/** @var string $title @var string $content @var array $menu @var string $active @var ?array $user @var array $flashes @var bool $isTest */
$assets = '';
if (function_exists('vite_tags')) {
    try {
        $assets = (string) vite_tags('frontend/admin.js');
    } catch (\Throwable) {
        $assets = '';
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title) ?> · Espace membres Aéroboat</title>
    <?= $assets ?>
</head>
<body class="admin">
<a class="skip-link" href="#contenu">Aller au contenu</a>
<header class="admin-header">
    <a class="brand" href="<?= e(Ui::u('')) ?>">Aéroboat France · Espace membres</a>
    <?php if ($user) : ?>
        <div class="who">
            <a href="<?= e(Ui::u('/profil')) ?>"><?= e((string) ($user['display_name'] ?? $user['username'] ?? 'Mon profil')) ?></a>
            <form method="post" action="<?= e(Ui::u('/logout')) ?>" class="inline-form">
                <?= Ui::csrf() ?>
                <button type="submit" class="btn btn-small">Se déconnecter</button>
            </form>
        </div>
    <?php endif; ?>
</header>
<div class="admin-body">
    <?php if ($menu) : ?>
        <nav class="admin-nav" aria-label="Navigation de l'espace membres">
            <ul>
                <?php foreach ($menu as $item) : ?>
                    <li><a href="<?= e($item['href']) ?>"<?= $active === $item['key'] ? ' aria-current="page"' : '' ?>><?= e($item['label']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>
    <?php endif; ?>
    <main id="contenu" class="admin-main" tabindex="-1">
        <?php foreach ($flashes as $f) : ?>
            <p class="alert alert-<?= $f['type'] === 'error' ? 'error' : 'ok' ?>" role="<?= $f['type'] === 'error' ? 'alert' : 'status' ?>"><?= e($f['text']) ?></p>
        <?php endforeach; ?>
        <?= $content ?>
    </main>
</div>
</body>
</html>
