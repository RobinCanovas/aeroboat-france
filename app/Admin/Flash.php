<?php
declare(strict_types=1);

namespace App\Admin;

/** Messages éphémères stockés dans la session admin. */
final class Flash
{
    public static function add(string $type, string $message): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }
        $_SESSION['abf_flash'][] = ['type' => $type === 'error' ? 'error' : 'ok', 'text' => $message];
    }

    /** @return list<array{type:string,text:string}> */
    public static function take(): array
    {
        if (session_status() !== PHP_SESSION_ACTIVE || empty($_SESSION['abf_flash'])) {
            return [];
        }
        $f = $_SESSION['abf_flash'];
        unset($_SESSION['abf_flash']);
        return is_array($f) ? array_values($f) : [];
    }
}
