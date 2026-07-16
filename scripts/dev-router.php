<?php
declare(strict_types=1);

/**
 * Routeur du serveur PHP intégré (développement local uniquement).
 * Usage : php -S 127.0.0.1:8000 -t public scripts/dev-router.php
 * Sert les fichiers statiques de public/, sinon délègue à public/index.php.
 */

$root = dirname(__DIR__) . '/public';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = is_string($path) ? rawurldecode($path) : '/';

// Jamais de fichier caché ni de traversée de répertoire.
if (str_contains($path, '..') || str_contains($path, "\0") || preg_match('#/\.#', $path) === 1) {
    http_response_code(404);
    echo 'Not found';
    return true;
}

$file = $root . $path;
if ($path !== '/' && is_file($file)) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    // Pas d'exécution de PHP dans uploads/ ; index.php passe par le front controller ci-dessous.
    if ($ext === 'php' && str_starts_with($path, '/uploads/')) {
        http_response_code(404);
        echo 'Not found';
        return true;
    }
    if ($ext !== 'php') {
        return false; // le serveur intégré sert le fichier statique avec le bon type MIME
    }
}

$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = $root . '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';
require $root . '/index.php';
