<?php
declare(strict_types=1);

/**
 * Migrations en ligne de commande.
 *   php database/migrate.php           joue les migrations sur la base configurée (.env)
 *   php database/migrate.php --status  état des migrations
 *   php database/migrate.php --seed    migrations puis seed (database/seed-content.php)
 *   php database/migrate.php --demo    SQLite temporaire : migrations ×2, seed ×2, requêtes de contrôle, suppression
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Db;
use App\Core\Migrator;
use App\Core\Seeder;

$args = array_slice($argv, 1);

if (in_array('--demo', $args, true)) {
    $file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'abf-demo-' . bin2hex(random_bytes(4)) . '.sqlite';
    $code = 0;
    try {
        Db::swap(Db::sqlite($file), 'abf_');
        $first = Migrator::run();
        $second = Migrator::run();
        echo 'Migrations jouées : ' . (implode(', ', $first) ?: 'aucune') . "\n";
        echo 'Second passage    : ' . count($second) . " migration(s) (attendu : 0)\n";
        if ($second !== [] || $first === []) {
            throw new RuntimeException('Migrations non idempotentes ou absentes.');
        }

        $content = Seeder::contentFile();
        $data = is_file($content) ? null : require dirname(__DIR__) . '/tests/core_seed_fixture.php';
        $s1 = Seeder::run($data);
        $s2 = Seeder::run($data);
        echo "Seed ({$s1['source']}) : équipe {$s1['team']}, partenaires {$s1['partners']}, articles {$s1['posts']}\n";
        echo "Second seed : équipe {$s2['team']}, partenaires {$s2['partners']}, articles {$s2['posts']} (attendu : 0)\n";
        if ($s2['team'] + $s2['partners'] + $s2['posts'] !== 0) {
            throw new RuntimeException('Seed non idempotent.');
        }

        $checks = [
            'tables abf_*' => (int) Db::one("SELECT COUNT(*) AS n FROM sqlite_master WHERE type = 'table' AND name LIKE 'abf\\_%' ESCAPE '\\'")['n'],
            'membres publiés' => (int) Db::one('SELECT COUNT(*) AS n FROM {t:team_members} WHERE published = 1')['n'],
            'partenaires publiés' => (int) Db::one('SELECT COUNT(*) AS n FROM {t:partners} WHERE published = 1')['n'],
            'articles visibles' => (int) Db::one(
                "SELECT COUNT(*) AS n FROM {t:posts} WHERE status = 'published' OR (status = 'scheduled' AND publish_at <= :now)",
                ['now' => Db::now()]
            )['n'],
            'ciel bleu publié (attendu 0)' => (int) Db::one("SELECT COUNT(*) AS n FROM {t:partners} WHERE published = 1 AND LOWER(name) LIKE '%ciel bleu%'")['n'],
            'Karim Benali (attendu 0)' => (int) Db::one("SELECT COUNT(*) AS n FROM {t:team_members} WHERE LOWER(name) LIKE '%benali%'")['n'],
            'tables mod759_ (attendu 0)' => (int) Db::one("SELECT COUNT(*) AS n FROM sqlite_master WHERE name LIKE 'mod759%'")['n'],
        ];
        foreach ($checks as $label => $n) {
            echo str_pad($label, 30) . " : $n\n";
        }
        if ($checks['ciel bleu publié (attendu 0)'] !== 0 || $checks['Karim Benali (attendu 0)'] !== 0 || $checks['tables mod759_ (attendu 0)'] !== 0) {
            throw new RuntimeException('Contrôle de contenu en échec.');
        }
        Db::insert('throttle', ['bucket' => 'demo', 'ip_hash' => str_repeat('0', 64), 'created_at' => time()]);
        echo "Insertion de contrôle : OK\nDémo réussie.\n";
    } catch (Throwable $e) {
        fwrite(STDERR, 'ÉCHEC : ' . $e->getMessage() . "\n");
        $code = 1;
    } finally {
        Db::swap(null);
        gc_collect_cycles();
        if (is_file($file) && !@unlink($file)) {
            fwrite(STDERR, "Fichier temporaire non supprimé : $file\n");
        }
    }
    exit($code);
}

if (in_array('--status', $args, true)) {
    foreach (Migrator::status() as $m) {
        echo ($m['applied'] ? '[x] ' : '[ ] ') . $m['migration'] . ($m['applied_at'] ? '  ' . $m['applied_at'] : '') . "\n";
    }
    exit(0);
}

$done = Migrator::run();
echo $done === [] ? "Base à jour.\n" : 'Migrations jouées : ' . implode(', ', $done) . "\n";
if (in_array('--seed', $args, true)) {
    $s = Seeder::run();
    echo "Seed ({$s['source']}) : équipe {$s['team']}, partenaires {$s['partners']}, articles {$s['posts']}\n";
}
