<?php
declare(strict_types=1);

namespace App\Admin;

use App\Core\Auth;
use App\Core\Config;
use App\Core\Migrator;
use App\Core\Throttle;

/** Connexion, déconnexion, installation initiale (/admin/setup) et profil. */
final class AuthController
{
    public static function login(Context $ctx): Reply
    {
        if ($ctx->user !== null) {
            return Reply::redirect(Ui::u(''));
        }
        $error = '';
        if ($ctx->isPost()) {
            if (!Throttle::hit('admin_login', 10, 900)) {
                $error = 'Trop de tentatives. Réessayez dans quelques minutes.';
            } elseif (Auth::attempt(trim($ctx->p('username')), $ctx->p('password'))) {
                return Reply::redirect(Ui::u(''));
            } else {
                $error = 'Identifiant ou mot de passe incorrect.';
            }
        }
        return Ui::wrap(null, 'Connexion', Ui::render('login', ['error' => $error, 'username' => trim($ctx->p('username'))]), '', $error !== '' ? 401 : 200);
    }

    public static function logout(Context $ctx): Reply
    {
        Auth::logout();
        return Reply::redirect(Ui::u('/login'));
    }

    /** Vrai si au moins un compte existe (faux si la table n'existe pas encore). */
    public static function accountExists(): bool
    {
        try {
            return Store::count('SELECT COUNT(*) FROM {t:users}') > 0;
        } catch (\Throwable) {
            return false;
        }
    }

    public static function setup(Context $ctx): Reply
    {
        $token = (string) Config::env('SETUP_TOKEN', '');
        if ($token === '' || self::accountExists()) {
            return Ui::error(null, 404, 'Page introuvable.');
        }
        $errors = [];
        $old = ['username' => '', 'display_name' => '', 'email' => ''];
        if ($ctx->isPost()) {
            $old = ['username' => trim($ctx->p('username')), 'display_name' => trim($ctx->p('display_name')), 'email' => trim($ctx->p('email'))];
            if (!Throttle::hit('admin_setup', 5, 3600)) {
                return Ui::wrap(null, 'Installation', '<h1>Trop de tentatives</h1><p>Réessayez dans une heure.</p>', '', 429);
            }
            if (!hash_equals($token, $ctx->p('setup_token'))) {
                $errors['setup_token'] = 'Jeton d\'installation incorrect.';
            }
            if (preg_match('/^[a-z0-9._-]{3,60}$/', $old['username']) !== 1) {
                $errors['username'] = 'Identifiant : 3 à 60 caractères (minuscules, chiffres, point, tiret, tiret bas).';
            }
            if ($old['display_name'] === '' || mb_strlen($old['display_name'], 'UTF-8') > 120) {
                $errors['display_name'] = 'Nom affiché obligatoire (120 caractères au maximum).';
            }
            if ($old['email'] !== '' && filter_var($old['email'], FILTER_VALIDATE_EMAIL) === false) {
                $errors['email'] = 'Adresse e-mail invalide.';
            }
            $pwErr = Validator::passwordError($ctx->p('password'), $ctx->p('password_confirm'));
            if ($pwErr !== null) {
                $errors['password'] = $pwErr;
            }
            if ($errors === []) {
                Migrator::run();
                foreach (['App\\Core\\Seeder', 'App\\Seeder'] as $seeder) {
                    if (class_exists($seeder)) {
                        $seeder::run();
                        break;
                    }
                }
                if (self::accountExists()) {
                    return Ui::error(null, 404, 'Page introuvable.');
                }
                Store::insert('users', [
                    'username' => $old['username'],
                    'display_name' => $old['display_name'],
                    'email' => $old['email'] === '' ? null : $old['email'],
                    'password_hash' => Passwords::hash($ctx->p('password')),
                    'role' => 'admin',
                    'active' => 1,
                    'created_at' => Time::nowUtc(),
                ]);
                Flash::add('ok', 'Installation terminée. Connectez-vous avec le compte créé, puis retirez SETUP_TOKEN du fichier .env.');
                return Reply::redirect(Ui::u('/login'));
            }
        }
        return Ui::wrap(null, 'Installation', Ui::render('setup', ['errors' => $errors, 'old' => $old]), '', $errors ? 422 : 200);
    }

    public static function profile(Context $ctx): Reply
    {
        $id = $ctx->userId();
        $row = Store::one('SELECT * FROM {t:users} WHERE id = ?', [$id]);
        if ($row === null) {
            return Ui::error($ctx, 404, 'Compte introuvable.');
        }
        $errors = [];
        $name = (string) $row['display_name'];
        if ($ctx->isPost()) {
            $name = trim($ctx->p('display_name'));
            if ($name === '' || mb_strlen($name, 'UTF-8') > 120) {
                $errors['display_name'] = 'Nom affiché obligatoire (120 caractères au maximum).';
            }
            $new = $ctx->p('new_password');
            $hash = null;
            if ($new !== '' || $ctx->p('current_password') !== '') {
                if (!password_verify($ctx->p('current_password'), (string) $row['password_hash'])) {
                    $errors['current_password'] = 'Mot de passe actuel incorrect.';
                } elseif (($err = Validator::passwordError($new, $ctx->p('new_password_confirm'))) !== null) {
                    $errors['new_password'] = $err;
                } else {
                    $hash = Passwords::hash($new);
                }
            }
            if ($errors === []) {
                $data = ['display_name' => $name];
                if ($hash !== null) {
                    $data['password_hash'] = $hash;
                }
                Store::update('users', $id, $data);
                Flash::add('ok', $hash !== null ? 'Profil et mot de passe mis à jour.' : 'Profil mis à jour.');
                return Reply::redirect(Ui::u('/profil'));
            }
        }
        return Ui::page($ctx, 'Mon profil', 'profile', ['name' => $name, 'row' => $row, 'errors' => $errors], 'profil', $errors ? 422 : 200);
    }
}
