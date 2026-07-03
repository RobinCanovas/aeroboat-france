<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Insère le contenu initial (database/seed-content.php : tableaux team, partners, posts). Idempotent :
 * une ligne déjà présente (même clé naturelle) n'est ni dupliquée ni écrasée (les modifications de l'admin priment).
 * Garde-fous : « Un morceau de ciel bleu » toujours published=0 ; « Karim Benali » jamais inséré.
 */
final class Seeder
{
    private const COLUMNS = [
        'team_members' => ['name', 'role_fr', 'role_en', 'bio_fr', 'bio_en', 'photo', 'photo_consent', 'linkedin', 'sort_order', 'published'],
        'partners' => ['name', 'category', 'description_fr', 'description_en', 'logo', 'logo_consent', 'url', 'sort_order', 'published'],
        'posts' => ['slug_fr', 'slug_en', 'title_fr', 'title_en', 'excerpt_fr', 'excerpt_en', 'body_fr', 'body_en', 'cover', 'cover_alt_fr', 'cover_alt_en', 'status', 'publish_at', 'author_id'],
    ];

    public static function contentFile(): string
    {
        return Config::root() . '/database/seed-content.php';
    }

    /**
     * @param array|null $data contenu à insérer ; null = database/seed-content.php
     * @return array{team:int,partners:int,posts:int,source:string}
     */
    public static function run(?array $data = null): array
    {
        $source = 'argument';
        if ($data === null) {
            $file = self::contentFile();
            if (!is_file($file)) {
                return ['team' => 0, 'partners' => 0, 'posts' => 0, 'source' => 'absent'];
            }
            $data = require $file;
            $source = 'seed-content.php';
            if (!is_array($data)) {
                throw new \RuntimeException('seed-content.php doit retourner un tableau.');
            }
        }
        $now = Db::now();
        $count = ['team' => 0, 'partners' => 0, 'posts' => 0, 'source' => $source];

        foreach (array_values($data['team'] ?? []) as $i => $row) {
            $row = self::filter('team_members', (array) $row);
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '' || stripos($name, 'karim benali') !== false) {
                continue;
            }
            if (Db::one('SELECT id FROM {t:team_members} WHERE name = :n', ['n' => $name])) {
                continue;
            }
            $row += ['sort_order' => ($i + 1) * 10, 'published' => 1];
            $row['photo_consent'] = (int) ($row['photo_consent'] ?? (empty($row['photo']) ? 0 : 1));
            $row['created_at'] = $row['updated_at'] = $now;
            Db::insert('team_members', $row);
            $count['team']++;
        }

        foreach (array_values($data['partners'] ?? []) as $i => $row) {
            $row = self::filter('partners', (array) $row);
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '' || Db::one('SELECT id FROM {t:partners} WHERE name = :n', ['n' => $name])) {
                continue;
            }
            $row += ['category' => 'support', 'sort_order' => ($i + 1) * 10, 'published' => 1];
            $row['logo_consent'] = (int) ($row['logo_consent'] ?? (empty($row['logo']) ? 0 : 1));
            if (stripos($name, 'morceau de ciel bleu') !== false) {
                $row['published'] = 0;
            }
            $row['created_at'] = $row['updated_at'] = $now;
            Db::insert('partners', $row);
            $count['partners']++;
        }

        foreach ($data['posts'] ?? [] as $row) {
            $row = self::filter('posts', (array) $row);
            $slug = trim((string) ($row['slug_fr'] ?? ''));
            if ($slug === '' || Db::one('SELECT id FROM {t:posts} WHERE slug_fr = :s', ['s' => $slug])) {
                continue;
            }
            $row += ['slug_en' => $slug, 'title_en' => $row['title_fr'] ?? '', 'body_en' => $row['body_fr'] ?? '', 'status' => 'published'];
            $row['body_fr'] = (string) ($row['body_fr'] ?? '');
            $row['body_en'] = (string) $row['body_en'];
            $row['created_at'] = $row['updated_at'] = $row['publish_at'] ?? $now;
            Db::insert('posts', $row);
            $count['posts']++;
        }
        return $count;
    }

    private static function filter(string $table, array $row): array
    {
        return array_intersect_key($row, array_flip(self::COLUMNS[$table]));
    }
}
