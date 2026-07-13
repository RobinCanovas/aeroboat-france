<?php
declare(strict_types=1);

namespace App\Admin;

use App\Core\Auth;

/** Hachage des mots de passe : délègue à Auth::hashPassword() du noyau s'il existe, sinon password_hash(). */
final class Passwords
{
    public static function hash(string $password): string
    {
        if (method_exists(Auth::class, 'hashPassword')) {
            return (string) Auth::hashPassword($password);
        }
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
