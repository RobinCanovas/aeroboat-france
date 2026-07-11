<?php
declare(strict_types=1);

namespace App\Admin;

use App\Core\Auth;
use App\Core\Csrf;

/** Requête admin : lit directement les superglobales (l'admin gère sa propre session). */
final class Context
{
    /**
     * @param array<string,mixed> $query
     * @param array<string,mixed> $post
     * @param array<string,mixed> $files
     * @param array<string,mixed>|null $user
     */
    public function __construct(
        public readonly string $method,
        public readonly array $query,
        public readonly array $post,
        public readonly array $files,
        public ?array $user = null,
    ) {
    }

    public static function fromGlobals(): self
    {
        return new self(
            strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')),
            $_GET,
            $_POST,
            $_FILES,
            Auth::user(),
        );
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    /** Paramètre d'URL en chaîne (tableaux ignorés). */
    public function q(string $key, string $default = ''): string
    {
        $v = $this->query[$key] ?? $default;
        return is_scalar($v) ? (string) $v : $default;
    }

    /** Champ de formulaire en chaîne (tableaux ignorés). */
    public function p(string $key, string $default = ''): string
    {
        $v = $this->post[$key] ?? $default;
        return is_scalar($v) ? (string) $v : $default;
    }

    public function role(): ?string
    {
        return isset($this->user['role']) ? (string) $this->user['role'] : null;
    }

    public function userId(): int
    {
        return (int) ($this->user['id'] ?? 0);
    }

    public function csrfOk(): bool
    {
        return Csrf::verifySession($this->p('_token'));
    }
}
