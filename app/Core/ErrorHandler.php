<?php
declare(strict_types=1);

namespace App\Core;

/** Erreurs → exceptions ; exceptions non capturées → journal + page 500 sans fuite (détail seulement en debug). */
final class ErrorHandler
{
    private static bool $registered = false;

    public static function register(): void
    {
        if (self::$registered) {
            return;
        }
        self::$registered = true;

        set_error_handler(static function (int $no, string $str, string $file = '', int $line = 0): bool {
            if (!(error_reporting() & $no)) {
                return false;
            }
            throw new \ErrorException($str, 0, $no, $file, $line);
        });

        set_exception_handler(static function (\Throwable $e): void {
            self::handle($e);
        });

        register_shutdown_function(static function (): void {
            $err = error_get_last();
            if ($err !== null && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                self::handle(new \ErrorException($err['message'], 0, $err['type'], $err['file'], $err['line']));
            }
        });
    }

    public static function handle(\Throwable $e): void
    {
        Logger::error(get_class($e) . ': ' . $e->getMessage(), ['at' => self::shortPath($e->getFile()) . ':' . $e->getLine()]);
        if (PHP_SAPI === 'cli') {
            fwrite(STDERR, get_class($e) . ': ' . $e->getMessage() . ' (' . self::shortPath($e->getFile()) . ':' . $e->getLine() . ")\n");
            exit(1);
        }
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        try {
            \App\Controllers\ErrorController::serverError($e);
        } catch (\Throwable $inner) {
            Logger::error('Échec de la page 500 : ' . $inner->getMessage());
            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: text/html; charset=utf-8');
            }
            echo '<!doctype html><meta charset="utf-8"><title>500</title><p>Erreur interne / Internal error.</p>';
        }
    }

    private static function shortPath(string $file): string
    {
        return str_replace([Config::root(), '\\'], ['', '/'], $file);
    }
}
