<?php
declare(strict_types=1);

/** Seed idempotent de la base configurée : php database/seed.php (lit database/seed-content.php). */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/app/bootstrap.php';

$s = App\Core\Seeder::run();
if ($s['source'] === 'absent') {
    fwrite(STDERR, "database/seed-content.php absent : rien à insérer.\n");
    exit(1);
}
echo "Seed : équipe {$s['team']}, partenaires {$s['partners']}, articles {$s['posts']} (lignes existantes conservées)\n";
