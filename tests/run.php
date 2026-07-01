<?php
declare(strict_types=1);

/**
 * Runner minimal (aucune dépendance). Exécute tests/*.php (sauf run.php et helpers).
 * Chaque test retourne un tableau de messages d'erreur (vide = OK) via une fonction `check()`.
 * Usage : php tests/run.php [filtre]
 */

define('ROOT', dirname(__DIR__));
require __DIR__ . '/helpers.php';

$filter = $argv[1] ?? '';
$files = glob(__DIR__ . '/*_test.php') ?: [];
sort($files);
$failed = 0;
$ran = 0;

foreach ($files as $file) {
    $name = basename($file, '_test.php');
    if ($filter !== '' && !str_contains($name, $filter)) {
        continue;
    }
    $ran++;
    $fn = require $file;
    $errors = is_callable($fn) ? $fn() : ['le fichier de test doit retourner une fonction'];
    if ($errors === []) {
        echo "  OK    $name\n";
        continue;
    }
    $failed++;
    echo "  ECHEC $name (" . count($errors) . ")\n";
    foreach (array_slice($errors, 0, 25) as $err) {
        echo "        - $err\n";
    }
    if (count($errors) > 25) {
        echo '        … et ' . (count($errors) - 25) . " autres\n";
    }
}

echo "\n$ran test(s), $failed en échec. PHP " . PHP_VERSION . "\n";
exit($failed === 0 ? 0 : 1);
