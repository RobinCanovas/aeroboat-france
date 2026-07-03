<?php
declare(strict_types=1);

/** Csrf HMAC sans cookie : valide, expiré, trop rapide, falsifié, autre formulaire, autre clé. */
return static function (): array {
    require_once ROOT . '/app/bootstrap.php';
    $C = App\Core\Csrf::class;
    App\Core\Config::set('APP_KEY', str_repeat('a1', 32));
    $errors = [];
    $now = time();

    $old = App\Core\Csrf::formToken('contact', $now - 10);
    if (App\Core\Csrf::checkForm('contact', $old, 3, 7200, $now) !== $C::OK) {
        $errors[] = 'jeton valide refusé';
    }
    if (!App\Core\Csrf::verifyForm('contact', App\Core\Csrf::formToken('contact', time() - 5))) {
        $errors[] = 'verifyForm() refuse un jeton valide';
    }
    if (App\Core\Csrf::checkForm('contact', App\Core\Csrf::formToken('contact', $now - 1), 3, 7200, $now) !== $C::TOO_FAST) {
        $errors[] = 'jeton trop rapide non détecté';
    }
    if (App\Core\Csrf::verifyForm('contact', App\Core\Csrf::formToken('contact'))) {
        $errors[] = 'verifyForm() accepte un envoi immédiat';
    }
    if (App\Core\Csrf::checkForm('contact', App\Core\Csrf::formToken('contact', $now - 8000), 3, 7200, $now) !== $C::EXPIRED) {
        $errors[] = 'jeton expiré accepté';
    }
    if (App\Core\Csrf::checkForm('contact', App\Core\Csrf::formToken('contact', $now + 600), 3, 7200, $now) !== $C::INVALID) {
        $errors[] = 'jeton daté du futur accepté';
    }
    [$ts, $mac] = explode('.', $old);
    $forged = [
        ($ts - 100) . '.' . $mac,
        $ts . '.' . strrev($mac),
        $ts . '.' . str_repeat('0', 64),
        $ts,
        '',
        'abc.def',
        $old . 'x',
    ];
    foreach ($forged as $f) {
        if (App\Core\Csrf::checkForm('contact', $f, 3, 7200, $now) !== $C::INVALID) {
            $errors[] = "jeton falsifié accepté : $f";
        }
    }
    if (App\Core\Csrf::checkForm('join', $old, 3, 7200, $now) !== $C::INVALID) {
        $errors[] = "jeton d'un autre formulaire accepté";
    }
    App\Core\Config::set('APP_KEY', str_repeat('b2', 32));
    if (App\Core\Csrf::checkForm('contact', $old, 3, 7200, $now) !== $C::INVALID) {
        $errors[] = 'jeton signé avec une autre clé accepté';
    }
    App\Core\Config::set('APP_KEY', 'court');
    try {
        App\Core\Csrf::formToken('contact');
        $errors[] = 'APP_KEY trop courte acceptée';
    } catch (RuntimeException $e) {
    }
    App\Core\Config::set('APP_KEY', str_repeat('a1', 32));

    // Jeton de session (admin), en CLI la session est un simple tableau.
    $t = App\Core\Csrf::sessionToken();
    if (!App\Core\Csrf::verifySession($t) || App\Core\Csrf::verifySession('x') || App\Core\Csrf::verifySession('')) {
        $errors[] = 'jeton de session mal vérifié';
    }
    return $errors;
};
