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

// Simule le sous-dossier de déploiement (ex. /test) : ABF_BASE=/test retire ce préfixe pour les fichiers statiques.
$base = rtrim((string) getenv('ABF_BASE'), '/');
$staticPath = ($base !== '' && str_starts_with($path, $base . '/')) ? substr($path, strlen($base)) : $path;
$file = $root . $staticPath;
if ($staticPath !== '/' && is_file($file)) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    // Pas d'exécution de PHP dans uploads/ ; index.php passe par le front controller ci-dessous.
    if ($ext === 'php' && str_starts_with($staticPath, '/uploads/')) {
        http_response_code(404);
        echo 'Not found';
        return true;
    }
    if ($ext !== 'php') {
        if ($staticPath === $path) {
            return false; // le serveur intégré sert le fichier statique avec le bon type MIME
        }
        // Chemin réécrit (préfixe simulé) : le serveur intégré ne sait pas le servir, on envoie le fichier nous-mêmes.
        $mimes = ['css' => 'text/css', 'js' => 'text/javascript', 'webp' => 'image/webp', 'png' => 'image/png', 'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg', 'svg' => 'image/svg+xml', 'ico' => 'image/x-icon', 'woff2' => 'font/woff2', 'json' => 'application/json',
            'webmanifest' => 'application/manifest+json', 'txt' => 'text/plain'];
        header('Content-Type: ' . ($mimes[$ext] ?? 'application/octet-stream'));
        header('Content-Length: ' . (string) filesize($file));
        readfile($file);
        return true;
    }
}

$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = $root . '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';
require $root . '/index.php';
