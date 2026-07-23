<?php
declare(strict_types=1);

use App\Admin\Context;
use App\Admin\Policy;
use App\Admin\Store;
use App\Admin\Validator;

/** Comptes de l'espace membres (administrateurs seulement). Pas de suppression : on désactive. */
return [
    'key' => 'comptes',
    'table' => 'users',
    'area' => 'users',
    'label' => 'Comptes',
    'singular' => 'compte',
    'title_field' => 'username',
    'can_delete' => false,
    'virtual' => ['password', 'password_confirm'],
    'defaults' => ['role' => 'editor', 'active' => 1],
    'fields' => [
        'username' => ['type' => 'text', 'label' => 'Identifiant', 'required' => true, 'max' => 60, 'min_length' => 3, 'help' => 'Minuscules, chiffres, point, tiret, tiret bas.'],
        'display_name' => ['type' => 'text', 'label' => 'Nom affiché', 'required' => true, 'max' => 120],
        'email' => ['type' => 'email', 'label' => 'E-mail', 'max' => 190],
        'role' => ['type' => 'select', 'label' => 'Rôle', 'required' => true, 'options' => Policy::ROLES],
        'active' => ['type' => 'bool', 'label' => 'Compte actif'],
        'password' => ['type' => 'password', 'label' => 'Mot de passe', 'required_on_create' => true, 'help' => '12 caractères minimum. À la modification : laissez vide pour ne pas le changer, ou saisissez un nouveau mot de passe pour le réinitialiser.'],
        'password_confirm' => ['type' => 'password', 'label' => 'Confirmation du mot de passe'],
    ],
    'groups' => [
        ['title' => 'Compte', 'fields' => ['username', 'display_name', 'email', 'role', 'active']],
        ['title' => 'Mot de passe', 'fields' => ['password', 'password_confirm']],
    ],
    'list' => [
        'columns' => ['username', 'display_name', 'role', 'active', 'last_login_at'],
        'search' => ['username', 'display_name', 'email'],
        'sort' => ['username', 'role', 'active', 'last_login_at'],
        'default_sort' => ['username', 'asc'],
        'filters' => ['role' => 'Rôle'],
        'per_page' => 30,
    ],
    'after_save' => static function (array $v, ?array $ex, int $id, Context $ctx): void {
        // Mot de passe changé pour le compte connecté : on garde sa session valide.
        if (isset($v['password_hash']) && $id === $ctx->userId() && method_exists(\App\Core\Auth::class, 'refreshFingerprint')) {
            \App\Core\Auth::refreshFingerprint();
        }
    },
    'before_save' => static function (array $v, ?array $ex, Context $ctx): array {
        $errors = [];
        $id = (int) ($ex['id'] ?? 0);

        if (preg_match('/^[a-z0-9._-]{3,60}$/', (string) $v['username']) !== 1) {
            $errors['username'] = 'Identifiant invalide : minuscules, chiffres, point, tiret, tiret bas (3 à 60).';
        } elseif (Store::count('SELECT COUNT(*) FROM {t:users} WHERE username = ? AND id <> ?', [$v['username'], $id]) > 0) {
            $errors['username'] = 'Cet identifiant existe déjà.';
        }

        $password = (string) ($v['password'] ?? '');
        if ($password !== '') {
            $err = Validator::passwordError($password, (string) ($v['password_confirm'] ?? ''));
            if ($err !== null) {
                $errors['password'] = $err;
            } else {
                $v['password_hash'] = \App\Admin\Passwords::hash($password);
            }
        } elseif ($ex === null) {
            $errors['password'] = 'Le mot de passe est obligatoire à la création.';
        }

        if ($ex !== null) {
            $admins = Store::count("SELECT COUNT(*) FROM {t:users} WHERE role = 'admin' AND active = 1");
            $err = Policy::accountChangeError($ctx->userId(), $ex, (string) $v['role'], (int) $v['active'], $admins);
            if ($err !== null) {
                $errors['role'] = $err;
            }
        }
        if ($ex === null) {
            $v['created_at'] = gmdate('Y-m-d H:i:s');
        }
        return [$v, $errors];
    },
];
