<?php
declare(strict_types=1);

/**
 * Amorçage commun (web, CLI, tests) : autoload PSR-4 maison, configuration, gestion d'erreurs, fonctions globales.
 * Ne démarre aucune session et n'émet aucun en-tête : c'est le rôle de App\Core\Kernel (web uniquement).
 */

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

spl_autoload_register(static function (string $class): void {
    if (strncmp($class, 'App\\', 4) !== 0) {
        return;
    }
    $relative = substr($class, 4);
    if (!preg_match('/^[A-Za-z0-9_\\\\]+$/', $relative)) {
        return;
    }
    $file = APP_ROOT . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

App\Core\Config::load();

date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');
error_reporting(E_ALL);
ini_set('display_errors', PHP_SAPI === 'cli' || App\Core\Config::debug() ? '1' : '0');
ini_set('log_errors', '1');

App\Core\ErrorHandler::register();

require_once APP_ROOT . '/app/Core/helpers.php';
