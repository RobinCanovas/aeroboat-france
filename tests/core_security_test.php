<?php
declare(strict_types=1);

/**
 * Corrections Phase 3 : Retention (purge + runDaily), Throttle purgé à chaque hit, Auth (couple identifiant/IP,
 * seuil global qui ralentit sans bloquer, durée max, empreinte du mot de passe, CSRF régénéré), Csrf maxAge 3600,
 * Config BOM, formulaires (consentement, restrict, notification sans donnée personnelle, CV orphelin), journaux.
 */
return static function (): array {
    require_once ROOT . '/app/bootstrap.php';
    $errors = [];
    App\Core\Config::set('APP_KEY', str_repeat('d4', 32));
    $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'abf-sec-' . bin2hex(random_bytes(4));
    @mkdir($tmp . '/cv', 0700, true);
    $file = $tmp . '/db.sqlite';
    try {
        App\Core\Db::swap(App\Core\Db::sqlite($file), 'abf_');
        App\Core\Migrator::run();
        $fmt = static fn (int $daysAgo): string => gmdate('Y-m-d H:i:s', time() - $daysAgo * 86400);

        // Config : BOM UTF-8 initial ignoré
        $env = App\Core\Config::parse("\xEF\xBB\xBFAPP_ENV=local\nB=2 # c\n");
        if (($env['APP_ENV'] ?? null) !== 'local' || ($env['B'] ?? null) !== '2') {
            $errors[] = 'Config::parse : BOM non ignoré';
        }

        // Csrf : maxAge par défaut 3600
        if (App\Core\Csrf::verifyForm('contact', App\Core\Csrf::formToken('contact', time() - 4000))) {
            $errors[] = 'Csrf : jeton de 4000 s accepté par défaut';
        }
        if (!App\Core\Csrf::verifyForm('contact', App\Core\Csrf::formToken('contact', time() - 3000))) {
            $errors[] = 'Csrf : jeton de 3000 s refusé';
        }

        // Throttle : entrées expirées purgées à chaque hit
        App\Core\Db::insert('throttle', ['bucket' => 'old', 'ip_hash' => 'x', 'created_at' => time() - 2 * 86400]);
        App\Core\Throttle::hit('t', 5, 60, '203.0.113.1');
        if (App\Core\Db::one("SELECT id FROM {t:throttle} WHERE bucket = 'old'") !== null) {
            $errors[] = 'Throttle::hit ne purge pas les entrées expirées';
        }

        // Retention::purge
        $msg = static fn (string $at): int => App\Core\Db::insert('messages', [
            'kind' => 'contact', 'name' => 'N', 'email' => 'n@x.fr', 'subject' => null, 'request_type' => null,
            'body' => 'b', 'locale' => 'fr', 'handled' => 0, 'consent_at' => $at, 'created_at' => $at,
        ]);
        $app = static fn (string $at, ?string $cv): int => App\Core\Db::insert('applications', [
            'first_name' => 'A', 'last_name' => 'B', 'email' => 'a@x.fr', 'school' => null, 'level' => null, 'skills' => null,
            'motivation' => 'm', 'availability' => null, 'cv_file' => $cv, 'consent_at' => $at, 'status' => 'new', 'notes' => null,
            'locale' => 'fr', 'created_at' => $at, 'updated_at' => $at,
        ]);
        $msg($fmt(400));
        $keptMsg = $msg($fmt(300));
        file_put_contents($tmp . '/cv/old.pdf', '%PDF-1');
        file_put_contents($tmp . '/cv/new.pdf', '%PDF-1');
        $app($fmt(800), 'old.pdf');
        $app($fmt(800), '../db.sqlite');
        $keptApp = $app($fmt(600), 'new.pdf');
        App\Core\Db::insert('throttle', ['bucket' => 'old', 'ip_hash' => 'x', 'created_at' => time() - 2 * 86400]);
        $r = App\Core\Retention::purge($tmp . '/cv');
        if ($r !== ['messages' => 1, 'applications' => 2, 'cv' => 1, 'throttle' => 1]) {
            $errors[] = 'Retention::purge : décomptes ' . json_encode($r);
        }
        if (is_file($tmp . '/cv/old.pdf') || !is_file($tmp . '/cv/new.pdf') || !is_file($file)) {
            $errors[] = 'Retention::purge : CV mal supprimés (ou chemin hors dossier suivi)';
        }
        if (App\Core\Db::one('SELECT id FROM {t:messages} WHERE id = ?', [$keptMsg]) === null
            || App\Core\Db::one('SELECT id FROM {t:applications} WHERE id = ?', [$keptApp]) === null) {
            $errors[] = 'Retention::purge supprime des lignes récentes';
        }

        // Retention::runDaily : une fois par 24 h
        $marker = $tmp . '/.last-retention';
        if (App\Core\Retention::runDaily($marker) === null || App\Core\Retention::runDaily($marker) !== null) {
            $errors[] = 'Retention::runDaily ne respecte pas 1 fois / 24 h';
        }
        file_put_contents($marker, (string) (time() - 90000));
        if (App\Core\Retention::runDaily($marker) === null) {
            $errors[] = 'Retention::runDaily ne repart pas après 24 h';
        }

        // Formulaires
        if (App\Controllers\FormController::needsConsent('rights') || !App\Controllers\FormController::needsConsent('join')
            || !App\Controllers\FormController::needsConsent('contact')) {
            $errors[] = 'consentement : rights ne doit pas l\'exiger, join/contact oui';
        }
        if (!in_array('restrict', App\Controllers\FormController::REQUEST_TYPES, true)) {
            $errors[] = 'request_type restrict absent';
        }
        foreach (['join', 'contact', 'rights'] as $f) {
            [$subject, $body] = App\Controllers\FormController::notification($f, 42);
            if (!str_contains($body, 'n°42') || !str_contains($body, '/admin/') || preg_match('/@|Jean|Dupont/', $subject . $body)) {
                $errors[] = "notification $f incorrecte";
            }
        }
        $cvDir = App\Core\Upload::cvDir();
        @mkdir($cvDir, 0700, true);
        $cvName = bin2hex(random_bytes(16)) . '.pdf';
        file_put_contents($cvDir . '/' . $cvName, '%PDF-1');
        try {
            App\Controllers\FormController::insertApplication(['first_name' => 'A', 'colonne_inconnue' => 'x'], $cvName, App\Core\Db::now());
            $errors[] = 'insertApplication aurait dû échouer';
        } catch (Throwable $e) {
        }
        if (is_file($cvDir . '/' . $cvName)) {
            @unlink($cvDir . '/' . $cvName);
            $errors[] = 'CV orphelin non supprimé après échec d\'écriture';
        }

        // Logger::exception : pas de message brut hors debug
        App\Core\Config::set('APP_DEBUG', '0');
        $log = App\Core\Config::root() . '/storage/logs/app-' . gmdate('Y-m') . '.log';
        $before = is_file($log) ? filesize($log) : 0;
        App\Core\Logger::exception('Test journal', new RuntimeException('secret-saisi@exemple.fr'));
        clearstatcache();
        $tail = is_file($log) ? (string) file_get_contents($log, false, null, $before) : '';
        if (!str_contains($tail, 'RuntimeException') || !str_contains($tail, 'core_security_test.php:') || str_contains($tail, 'secret-saisi')) {
            $errors[] = 'Logger::exception : format incorrect ou message brut journalisé';
        }

        // Auth
        App\Core\Db::insert('users', [
            'username' => 'bureau', 'display_name' => 'Bureau', 'email' => null,
            'password_hash' => App\Core\Auth::hashPassword('Correct-horse-9'), 'role' => 'admin', 'active' => 1,
            'last_login_at' => null, 'created_at' => App\Core\Db::now(),
        ]);
        $_SERVER['REMOTE_ADDR'] = '198.51.100.20';
        for ($i = 0; $i < 5; $i++) {
            App\Core\Auth::attempt('bureau', 'faux');
        }
        if (!App\Core\Auth::isLocked('bureau') || App\Core\Auth::attempt('bureau', 'Correct-horse-9')) {
            $errors[] = 'Auth : couple (identifiant, IP) non bloqué après 5 échecs';
        }
        $_SERVER['REMOTE_ADDR'] = '198.51.100.21';
        $_SESSION['_csrf'] = 'ancien';
        if (!App\Core\Auth::attempt('bureau', 'Correct-horse-9')) {
            $errors[] = 'Auth : une autre IP est bloquée pour le même identifiant';
        }
        if (isset($_SESSION['_csrf'])) {
            $errors[] = 'Auth : jeton CSRF de session non régénéré';
        }
        if (App\Core\Auth::user() === null || isset(App\Core\Auth::user()['password_hash'])) {
            $errors[] = 'Auth::user() absent ou expose password_hash';
        }
        // Changement de mot de passe : les autres sessions tombent ; refreshFingerprint() garde la sienne.
        $uid = (int) App\Core\Auth::user()['id'];
        $saved = $_SESSION;
        App\Core\Db::update('users', $uid, ['password_hash' => App\Core\Auth::hashPassword('Nouveau-mot-2')]);
        App\Core\Auth::refreshFingerprint();
        if (App\Core\Auth::user() === null) {
            $errors[] = 'Auth::refreshFingerprint inopérant';
        }
        $_SESSION = $saved;
        $reset = (new ReflectionClass(App\Core\Auth::class))->getProperty('loaded');
        $reset->setValue(null, false);
        if (App\Core\Auth::user() !== null) {
            $errors[] = 'Auth : ancienne session valide après changement de mot de passe';
        }
        // Durée maximale 12 h
        $_SESSION = $saved;
        App\Core\Auth::refreshFingerprint();
        $_SESSION['auth_at'] = time() - 13 * 3600;
        $reset->setValue(null, false);
        if (App\Core\Auth::user() !== null) {
            $errors[] = 'Auth : session de plus de 12 h acceptée';
        }
        // Seuil global : ralentit, ne bloque pas
        for ($i = 0; $i < 50; $i++) {
            $_SERVER['REMOTE_ADDR'] = '192.0.2.' . (int) ($i / 4);
            App\Core\Auth::attempt('bureau', 'faux');
        }
        $_SERVER['REMOTE_ADDR'] = '203.0.113.99';
        if (!App\Core\Auth::isSlowed('bureau') || App\Core\Auth::isLocked('bureau') || !App\Core\Auth::attempt('bureau', 'Nouveau-mot-2')) {
            $errors[] = 'Auth : le seuil global bloque au lieu de ralentir';
        }
        App\Core\Auth::logout();
    } catch (Throwable $e) {
        $errors[] = get_class($e) . ' : ' . $e->getMessage() . ' @' . basename($e->getFile()) . ':' . $e->getLine();
    } finally {
        App\Core\Config::set('APP_DEBUG', null);
        App\Core\Db::swap(null);
        gc_collect_cycles();
        foreach ([$tmp . '/cv', $tmp] as $d) {
            foreach (is_dir($d) ? (scandir($d) ?: []) : [] as $f) {
                if (is_file($d . '/' . $f)) {
                    @unlink($d . '/' . $f);
                }
            }
        }
        @rmdir($tmp . '/cv');
        @rmdir($tmp);
    }
    return $errors;
};
