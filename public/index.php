<?php
declare(strict_types=1);

// Serveur intégré de PHP (php -S … -t public public/index.php) : les fichiers existants sont servis tels quels.
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . rawurldecode((string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH));
    if ($file !== __DIR__ . '/' && is_file($file) && !str_contains($file, '..') && !preg_match('#/uploads/.*\.ph(p\d?|ar|tml)$#i', $file)) {
        return false;
    }
}

require dirname(__DIR__) . '/app/bootstrap.php';

App\Core\Kernel::handle();
