<?php
declare(strict_types=1);

/**
 * Sur SQLite temporaire : Migrator idempotent (2 passages), status(), jetons, Db::insert/update/delete,
 * préfixe mod759_ refusé, Seeder idempotent et garde-fous, Throttle (limite, IP hachée, purge), Auth.
 */
return static function (): array {
    require_once ROOT . '/app/bootstrap.php';
    $errors = [];
    App\Core\Config::set('APP_KEY', str_repeat('c3', 32));
    $file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'abf-test-' . bin2hex(random_bytes(4)) . '.sqlite';
    try {
        App\Core\Db::swap(App\Core\Db::sqlite($file), 'abf_');

        // Migrator
        $first = App\Core\Migrator::run();
        $second = App\Core\Migrator::run();
        if ($first === [] || $second !== []) {
            $errors[] = 'Migrator non idempotent (' . count($first) . ' puis ' . count($second) . ')';
        }
        foreach (App\Core\Migrator::status() as $m) {
            if (!$m['applied']) {
                $errors[] = 'status() : migration non appliquée ' . $m['migration'];
            }
        }
        $sql = implode("\n", App\Core\Migrator::statements("CREATE TABLE {{PREFIX}}x (id {{PK}}) {{OPTS}};\n-- commentaire\nCREATE INDEX {{PREFIX}}i ON {{PREFIX}}x (id);", 'mysql'));
        if (!str_contains($sql, 'abf_x (id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY) ENGINE=InnoDB') || str_contains($sql, 'commentaire')) {
            $errors[] = 'rendu MySQL des jetons incorrect';
        }
        try {
            App\Core\Db::checkPrefix('mod759_');
            $errors[] = 'préfixe mod759_ accepté';
        } catch (RuntimeException $e) {
        }

        // Db CRUD
        $id = App\Core\Db::insert('messages', [
            'kind' => 'contact', 'name' => 'A', 'email' => 'a@b.fr', 'subject' => null, 'request_type' => null,
            'body' => "x'); DROP TABLE abf_users; --", 'locale' => 'fr', 'handled' => 0,
            'consent_at' => App\Core\Db::now(), 'created_at' => App\Core\Db::now(),
        ]);
        App\Core\Db::update('messages', $id, ['handled' => 1]);
        $row = App\Core\Db::one('SELECT * FROM {t:messages} WHERE id = :id', ['id' => $id]);
        if (($row['handled'] ?? null) != 1 || !str_contains((string) $row['body'], 'DROP TABLE')) {
            $errors[] = 'Db insert/update/one incorrects';
        }
        App\Core\Db::delete('messages', $id);
        if (App\Core\Db::one('SELECT id FROM {t:messages} WHERE id = ?', [$id]) !== null) {
            $errors[] = 'Db::delete inopérant';
        }
        try {
            App\Core\Db::insert('messages', ['name; DROP' => 'x']);
            $errors[] = 'nom de colonne non validé';
        } catch (InvalidArgumentException $e) {
        }

        // Seeder
        $data = require ROOT . '/tests/core_seed_fixture.php';
        $s1 = App\Core\Seeder::run($data);
        $s2 = App\Core\Seeder::run($data);
        if ($s1['posts'] !== 2 || $s2['team'] + $s2['partners'] + $s2['posts'] !== 0) {
            $errors[] = 'Seeder non idempotent';
        }
        if (App\Core\Db::one("SELECT id FROM {t:team_members} WHERE name = 'Karim Benali'") !== null) {
            $errors[] = 'Karim Benali inséré';
        }
        if ((int) (App\Core\Db::one("SELECT published FROM {t:partners} WHERE name = 'Un morceau de ciel bleu'")['published'] ?? 1) !== 0) {
            $errors[] = 'Un morceau de ciel bleu publié';
        }
        $visible = App\Controllers\Controller::latestPosts(10);
        if (count($visible) !== 1 || $visible[0]['slug_fr'] !== 'article-test') {
            $errors[] = 'visibilité des articles programmés incorrecte';
        }

        // Throttle
        for ($i = 0; $i < 3; $i++) {
            if (!App\Core\Throttle::hit('t_form', 3, 3600, '203.0.113.7')) {
                $errors[] = "Throttle refuse le passage $i";
            }
        }
        if (App\Core\Throttle::hit('t_form', 3, 3600, '203.0.113.7')) {
            $errors[] = 'Throttle : limite non appliquée';
        }
        if (!App\Core\Throttle::hit('t_form', 3, 3600, '203.0.113.8')) {
            $errors[] = 'Throttle : une autre IP est bloquée';
        }
        if (!App\Core\Throttle::hit('t_other', 3, 3600, '203.0.113.7')) {
            $errors[] = 'Throttle : un autre compartiment est bloqué';
        }
        if (App\Core\Db::one("SELECT id FROM {t:throttle} WHERE ip_hash LIKE '%203.0.113%'") !== null) {
            $errors[] = 'IP stockée en clair';
        }
        App\Core\Db::run('UPDATE {t:throttle} SET created_at = :t', ['t' => time() - 2 * 86400]);
        if (App\Core\Throttle::purge() < 5 || App\Core\Db::one('SELECT COUNT(*) AS n FROM {t:throttle}')['n'] != 0) {
            $errors[] = 'Throttle::purge inopérant';
        }

        // Auth (session CLI = tableau)
        App\Core\Db::insert('users', [
            'username' => 'bureau', 'display_name' => 'Bureau', 'email' => null,
            'password_hash' => App\Core\Auth::hashPassword('Correct-horse-9'), 'role' => 'editor', 'active' => 1,
            'last_login_at' => null, 'created_at' => App\Core\Db::now(),
        ]);
        $_SERVER['REMOTE_ADDR'] = '198.51.100.1';
        if (App\Core\Auth::attempt('bureau', 'faux')) {
            $errors[] = 'Auth accepte un mauvais mot de passe';
        }
        if (!App\Core\Auth::attempt('bureau', 'Correct-horse-9') || !App\Core\Auth::hasRole('editor') || App\Core\Auth::hasRole('admin')) {
            $errors[] = 'Auth : connexion ou rôles incorrects';
        }
        for ($i = 0; $i < 6; $i++) {
            App\Core\Auth::attempt('bureau', 'faux');
        }
        if (App\Core\Auth::attempt('bureau', 'Correct-horse-9') || App\Core\Auth::lastError() !== 'locked') {
            $errors[] = 'Auth : verrouillage progressif absent';
        }
        App\Core\Auth::logout();
    } catch (Throwable $e) {
        $errors[] = get_class($e) . ' : ' . $e->getMessage();
    } finally {
        App\Core\Db::swap(null);
        gc_collect_cycles();
        @unlink($file);
    }
    return $errors;
};
