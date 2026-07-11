<?php
declare(strict_types=1);

namespace App\Admin;

/** Réponse HTTP minimale produite par les contrôleurs admin (envoyée par Router::send). */
final class Reply
{
    /** @param array<string,string> $headers */
    public function __construct(
        public readonly int $status = 200,
        public readonly string $body = '',
        public readonly array $headers = [],
        public readonly ?string $file = null,
    ) {
    }

    public static function html(string $body, int $status = 200): self
    {
        return new self($status, $body, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public static function redirect(string $url, int $status = 303): self
    {
        return new self($status, '', ['Location' => $url]);
    }

    public static function text(string $body, int $status = 200): self
    {
        return new self($status, $body, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    /** Téléchargement d'un contenu en mémoire. */
    public static function download(string $body, string $filename, string $mime): self
    {
        return new self(200, $body, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'attachment; filename="' . self::safeName($filename) . '"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /** Téléchargement d'un fichier disque (chemin déjà validé par l'appelant). */
    public static function fileDownload(string $path, string $filename, string $mime): self
    {
        return new self(200, '', [
            'Content-Type' => $mime,
            'Content-Disposition' => 'attachment; filename="' . self::safeName($filename) . '"',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Length' => (string) filesize($path),
        ], $path);
    }

    private static function safeName(string $name): string
    {
        return preg_replace('/[^A-Za-z0-9._-]/', '_', $name) ?? 'fichier';
    }
}
