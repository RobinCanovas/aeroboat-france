<?php
declare(strict_types=1);

namespace App\Admin;

/** Règles de rôle (pures, testables). admin = bureau (tout) ; editor = rédacteur. */
final class Policy
{
    public const ROLES = ['admin' => 'Bureau (administrateur)', 'editor' => 'Rédacteur'];

    /** Zones accessibles par rôle. */
    private const AREAS = [
        'dashboard' => ['admin', 'editor'],
        'profile' => ['admin', 'editor'],
        'posts' => ['admin', 'editor'],
        'team' => ['admin', 'editor'],
        'partners' => ['admin', 'editor'],
        'modules' => ['admin', 'editor'],
        'applications' => ['admin'],
        'messages' => ['admin'],
        'users' => ['admin'],
        'purge' => ['admin'],
    ];

    public static function allowed(?string $role, string $area): bool
    {
        if ($role === null || !isset(self::AREAS[$area])) {
            return false;
        }
        return in_array($role, self::AREAS[$area], true);
    }

    /**
     * Vérifie une modification de compte. Retourne un message d'erreur ou null si autorisée.
     *
     * @param array{id:int|string,role:string,active:int|string} $target état actuel du compte modifié
     * @param int $activeAdmins nombre d'administrateurs actifs AVANT modification (cible comprise)
     */
    public static function accountChangeError(int $actorId, array $target, string $newRole, int $newActive, int $activeAdmins): ?string
    {
        if (!isset(self::ROLES[$newRole])) {
            return 'Rôle inconnu.';
        }
        if ($actorId === (int) $target['id'] && $newActive === 0) {
            return 'Vous ne pouvez pas désactiver votre propre compte.';
        }
        $wasActiveAdmin = $target['role'] === 'admin' && (int) $target['active'] === 1;
        $staysActiveAdmin = $newRole === 'admin' && $newActive === 1;
        if ($wasActiveAdmin && !$staysActiveAdmin && $activeAdmins <= 1) {
            return 'Action impossible : il doit rester au moins un administrateur actif.';
        }
        return null;
    }
}
