<?php
declare(strict_types=1);

/** Journal sans message brut, connexion limitée par le seul anti-bruteforce du Core, seuils de purge alignés sur Retention. */
return static function (): array {
    require_once ROOT . '/app/bootstrap.php';
    $errors = [];

    // 1. Aucun journal du périmètre n'écrit le message brut d'une exception.
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(ROOT . '/app/Admin', FilesystemIterator::SKIP_DOTS));
    $files = [];
    foreach ($it as $f) {
        if ($f->isFile() && $f->getExtension() === 'php') {
            $files[] = $f->getPathname();
        }
    }
    foreach (glob(ROOT . '/app/Modules/*/*.php') ?: [] as $f) {
        $files[] = $f;
    }
    foreach ($files as $f) {
        if (str_contains((string) file_get_contents($f), 'getMessage()')) {
            $errors[] = 'getMessage() dans ' . basename($f) . ' : utiliser Logger::exception()';
        }
    }
    if (!str_contains((string) file_get_contents(ROOT . '/app/Admin/Router.php'), 'Logger::exception(')) {
        $errors[] = 'Router::logError doit passer par Logger::exception()';
    }

    // 2. Plus de compteur de connexions pré-compté ; Auth::attempt est bien le garde-fou (verrou « locked »).
    $auth = (string) file_get_contents(ROOT . '/app/Admin/AuthController.php');
    if (str_contains($auth, "'admin_login'")) {
        $errors[] = 'le compteur admin_login (connexions réussies comprises) doit être supprimé';
    }
    if (!str_contains($auth, 'Auth::attempt(') || !str_contains($auth, "lastError() === 'locked'")) {
        $errors[] = 'le POST de connexion doit passer par Auth::attempt() et refléter le verrou';
    }

    // 3. Aperçu de purge = seuils du Core.
    $now = new DateTimeImmutable('2026-06-15 10:00:00', new DateTimeZone('Europe/Paris'));
    $mine = \App\Admin\PurgeController::cutoffs($now);
    $core = \App\Core\Retention::cutoffs($now);
    if ($mine !== $core) {
        $errors[] = 'seuils de purge admin différents de Retention::cutoffs()';
    }
    if ($mine['messages'] !== '2025-06-15 08:00:00' || $mine['applications'] !== '2024-06-15 08:00:00') {
        $errors[] = 'seuils UTC inattendus : ' . json_encode($mine);
    }
    return $errors;
};
