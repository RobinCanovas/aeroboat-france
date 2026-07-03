<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Gabarits PHP : app/views/{tpl}.php. render() enveloppe dans app/views/layout.php (sauf `_layout` === false) ;
 * le gabarit de mise en page reçoit les mêmes variables plus `$content` (HTML de la vue).
 */
final class View
{
    public static function path(string $tpl): string
    {
        if (!preg_match('#^[a-z0-9_\-]+(?:/[a-z0-9_\-]+)*$#i', $tpl)) {
            throw new \InvalidArgumentException('Nom de gabarit invalide.');
        }
        return Config::root() . '/app/views/' . $tpl . '.php';
    }

    public static function exists(string $tpl): bool
    {
        return is_file(self::path($tpl));
    }

    public static function render(string $tpl, array $data = []): string
    {
        $content = self::partial($tpl, $data);
        if (($data['_layout'] ?? true) === false) {
            return $content;
        }
        $layout = is_string($data['_layout'] ?? null) ? $data['_layout'] : 'layout';
        return self::partial($layout, ['content' => $content] + $data);
    }

    public static function partial(string $tpl, array $data = []): string
    {
        $file = self::path($tpl);
        if (!is_file($file)) {
            throw new \RuntimeException('Gabarit introuvable : ' . $tpl);
        }
        return (static function (string $__file, array $__data): string {
            extract($__data, EXTR_SKIP);
            ob_start();
            try {
                include $__file;
            } catch (\Throwable $e) {
                ob_end_clean();
                throw $e;
            }
            return (string) ob_get_clean();
        })($file, $data);
    }
}
