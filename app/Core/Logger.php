<?php
declare(strict_types=1);

namespace App\Core;

/** Journal applicatif : storage/logs/app-AAAA-MM.log. Ne jamais y écrire de secret ni de donnée personnelle. */
final class Logger
{
    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write('WARN', $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message, $context);
    }

    public static function debug(string $message, array $context = []): void
    {
        if (Config::debug()) {
            self::write('DEBUG', $message, $context);
        }
    }

    private static function write(string $level, string $message, array $context): void
    {
        $dir = Config::root() . '/storage/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $line = gmdate('Y-m-d H:i:s') . " [$level] " . str_replace(["\r", "\n"], ' ', $message);
        if ($context !== []) {
            $line .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
        }
        @file_put_contents($dir . '/app-' . gmdate('Y-m') . '.log', $line . "\n", FILE_APPEND | LOCK_EX);
    }
}
