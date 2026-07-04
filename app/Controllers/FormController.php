<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Csrf;
use App\Core\Db;
use App\Core\Lang;
use App\Core\Logger;
use App\Core\Mailer;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Throttle;
use App\Core\Upload;
use App\Core\UploadError;

/**
 * Formulaires publics sans cookie (CONTRACTS §4) : jeton HMAC, pot de miel « website », délai minimum 3 s,
 * 5 envois / heure / IP hachée / formulaire, consentement horodaté, écriture en base, mail best effort.
 * Vues : pages/join|contact|rights avec `errors` (champ → clé lang ; erreur globale sous `_form`) et `sent`.
 */
final class FormController extends Controller
{
    public const MIN_DELAY = 3;
    public const RATE_LIMIT = 5;
    public const RATE_WINDOW = 3600;
    public const REQUEST_TYPES = ['access', 'rectify', 'erase', 'object', 'portability'];

    public static function join(array $params = []): void
    {
        $rules = [
            'first_name' => [true, 100], 'last_name' => [true, 100], 'email' => [true, 190, 'email'],
            'school' => [false, 160], 'level' => [false, 100], 'skills' => [false, 2000],
            'motivation' => [true, 5000], 'availability' => [false, 250],
        ];
        self::handle('join', $rules, static function (array $v, array &$errors): ?callable {
            $cv = Request::file('cv');
            $cvName = null;
            if ($cv !== null) {
                try {
                    $cvName = Upload::pdf($cv);
                } catch (UploadError $e) {
                    $errors['cv'] = $e->getMessage();
                    return null;
                }
            }
            return static function (string $now) use ($v, $cvName): void {
                Db::insert('applications', $v + [
                    'cv_file' => $cvName, 'consent_at' => $now, 'status' => 'new', 'notes' => null,
                    'locale' => Lang::current(), 'created_at' => $now, 'updated_at' => $now,
                ]);
                self::notify('Nouvelle candidature', "Candidature de {$v['first_name']} {$v['last_name']} <{$v['email']}>.\n"
                    . ($cvName ? "CV joint (voir l'espace membres).\n" : '') . "\nDétails dans l'espace membres.", $v['email']);
            };
        });
    }

    public static function contact(array $params = []): void
    {
        $rules = ['name' => [true, 120], 'email' => [true, 190, 'email'], 'subject' => [false, 200], 'body' => [true, 5000]];
        self::handle('contact', $rules, static function (array $v, array &$errors): ?callable {
            return static function (string $now) use ($v): void {
                Db::insert('messages', $v + [
                    'kind' => 'contact', 'request_type' => null, 'locale' => Lang::current(),
                    'handled' => 0, 'consent_at' => $now, 'created_at' => $now,
                ]);
                self::notify('Contact : ' . ($v['subject'] !== '' ? $v['subject'] : '(sans objet)'), "De : {$v['name']} <{$v['email']}>\n\n{$v['body']}", $v['email']);
            };
        });
    }

    public static function rights(array $params = []): void
    {
        $rules = ['name' => [true, 120], 'email' => [true, 190, 'email'], 'request_type' => [true, 20, 'type'], 'body' => [true, 5000]];
        self::handle('rights', $rules, static function (array $v, array &$errors): ?callable {
            return static function (string $now) use ($v): void {
                Db::insert('messages', $v + [
                    'kind' => 'rights', 'subject' => null, 'locale' => Lang::current(),
                    'handled' => 0, 'consent_at' => $now, 'created_at' => $now,
                ]);
                self::notify('Demande RGPD (' . $v['request_type'] . ')', "De : {$v['name']} <{$v['email']}>\nType : {$v['request_type']}\n\n{$v['body']}\n\nDélai légal de réponse : un mois.", $v['email']);
            };
        });
    }

    /**
     * @param array<string,array{0:bool,1:int,2?:string}> $rules champ => [obligatoire, longueur max, type]
     * @param callable(array,array&):?callable $prepare valide les extras et retourne l'écriture à exécuter
     */
    private static function handle(string $form, array $rules, callable $prepare): void
    {
        $self = Router::url($form);
        if (Request::method() !== 'POST') {
            self::page($form, [], Request::input('sent') === '1');
            return;
        }

        // Pot de miel : rejet silencieux, réponse identique à un succès.
        $hp = Request::input('website', '');
        if (!is_string($hp) || $hp !== '') {
            Response::redirect($self . '?sent=1', 303);
        }

        $token = Request::input('_token', '');
        $state = Csrf::checkForm($form, is_string($token) ? $token : '', self::MIN_DELAY);
        if ($state !== Csrf::OK) {
            self::page($form, ['_form' => $state === Csrf::TOO_FAST ? 'forms.errors.too_fast' : 'forms.errors.csrf'], false, 422);
            return;
        }

        $values = [];
        $errors = [];
        foreach ($rules as $field => $rule) {
            $v = Request::str($field);
            $v = (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $v);
            if (!mb_check_encoding($v, 'UTF-8')) {
                $v = '';
            }
            $values[$field] = $v;
            if ($v === '') {
                if ($rule[0]) {
                    $errors[$field] = 'forms.errors.required';
                }
                continue;
            }
            if (mb_strlen($v) > $rule[1]) {
                $errors[$field] = 'forms.errors.too_long';
            } elseif (($rule[2] ?? '') === 'email' && !filter_var($v, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = 'forms.errors.email';
            } elseif (($rule[2] ?? '') === 'type' && !in_array($v, self::REQUEST_TYPES, true)) {
                $errors[$field] = 'forms.errors.required';
            }
        }
        if (Request::input('consent') === null || Request::input('consent') === '' || Request::input('consent') === '0') {
            $errors['consent'] = 'forms.errors.consent';
        }
        if ($errors !== []) {
            self::page($form, $errors, false, 422);
            return;
        }

        if (!Throttle::hit('form_' . $form, self::RATE_LIMIT, self::RATE_WINDOW)) {
            self::page($form, ['_form' => 'forms.errors.rate_limit'], false, 429);
            return;
        }

        $write = $prepare($values, $errors);
        if ($errors !== [] || $write === null) {
            self::page($form, $errors ?: ['_form' => 'forms.errors.generic'], false, 422);
            return;
        }
        try {
            $write(Db::now());
        } catch (\Throwable $e) {
            Logger::error('Écriture du formulaire ' . $form . ' en échec : ' . $e->getMessage());
            self::page($form, ['_form' => 'forms.errors.generic'], false, 500);
            return;
        }
        Response::redirect($self . '?sent=1', 303);
    }

    private static function page(string $form, array $errors, bool $sent, int $code = 200): void
    {
        $meta = self::meta(Lang::t($form . '.hero.title'), Lang::t($form . '.hero.lead'));
        self::display('pages/' . $form, $meta, ['errors' => $errors, 'sent' => $sent], $code);
    }

    /** Notification best effort : n'interrompt jamais l'enregistrement. */
    private static function notify(string $subject, string $body, string $replyTo): void
    {
        $to = (string) Config::env('MAIL_TO', '');
        if ($to !== '') {
            Mailer::send($to, '[Site] ' . $subject, $body, $replyTo);
        }
    }
}
