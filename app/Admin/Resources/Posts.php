<?php
declare(strict_types=1);

use App\Admin\Context;
use App\Admin\Slug;
use App\Admin\Store;
use App\Admin\Time;

/** Articles (actualités) : FR/EN côte à côte, brouillon / publié / programmé. */

/** Cherche un slug libre dans la colonne donnée (suffixe -2, -3… si nécessaire). */
$uniqueSlug = static function (string $col, string $base, int $id): string {
    $slug = $base;
    $n = 2;
    while (Store::count('SELECT COUNT(*) FROM {t:posts} WHERE ' . $col . ' = ? AND id <> ?', [$slug, $id]) > 0) {
        $slug = substr($base, 0, 150) . '-' . $n++;
    }
    return $slug;
};

return [
    'key' => 'articles',
    'table' => 'posts',
    'area' => 'posts',
    'label' => 'Articles',
    'singular' => 'article',
    'title_field' => 'title_fr',
    'timestamps' => true,
    'defaults' => ['status' => 'draft'],
    'fields' => [
        'title_fr' => ['type' => 'text', 'label' => 'Titre (français)', 'required' => true, 'max' => 200],
        'slug_fr' => ['type' => 'text', 'label' => 'Adresse (français)', 'max' => 160, 'help' => 'Laissez vide : générée depuis le titre. Minuscules, chiffres et tirets.'],
        'excerpt_fr' => ['type' => 'textarea', 'label' => 'Résumé (français)', 'max' => 400, 'rows' => 3],
        'body_fr' => ['type' => 'markdown', 'label' => 'Texte (français, Markdown)', 'required' => true, 'rows' => 14],
        'cover_alt_fr' => ['type' => 'text', 'label' => 'Texte alternatif de la couverture (français)', 'max' => 200],
        'title_en' => ['type' => 'text', 'label' => 'Titre (anglais)', 'max' => 200, 'help' => 'Laissez vide : le titre français est repris.'],
        'slug_en' => ['type' => 'text', 'label' => 'Adresse (anglais)', 'max' => 160, 'help' => 'Laissez vide : générée depuis le titre.'],
        'excerpt_en' => ['type' => 'textarea', 'label' => 'Résumé (anglais)', 'max' => 400, 'rows' => 3],
        'body_en' => ['type' => 'markdown', 'label' => 'Texte (anglais, Markdown)', 'rows' => 14, 'help' => 'Laissez vide : la version française sera affichée.'],
        'cover_alt_en' => ['type' => 'text', 'label' => 'Texte alternatif de la couverture (anglais)', 'max' => 200],
        'cover' => ['type' => 'image', 'label' => 'Image de couverture', 'subdir' => 'posts', 'help' => 'JPEG, PNG ou WebP ; convertie en WebP.'],
        'status' => ['type' => 'select', 'label' => 'Statut', 'required' => true, 'options' => ['draft' => 'Brouillon', 'published' => 'Publié', 'scheduled' => 'Programmé']],
        'publish_at' => ['type' => 'datetime', 'label' => 'Date de publication (heure de Paris)', 'help' => 'Obligatoire pour un article programmé ; vide pour un article publié : maintenant.'],
    ],
    'groups' => [
        ['title' => 'Publication', 'fields' => ['status', 'publish_at', 'cover', 'cover_alt_fr', 'cover_alt_en']],
        ['title' => 'Français', 'class' => 'col', 'fields' => ['title_fr', 'slug_fr', 'excerpt_fr', 'body_fr']],
        ['title' => 'Anglais', 'class' => 'col', 'fields' => ['title_en', 'slug_en', 'excerpt_en', 'body_en']],
    ],
    'preview' => ['body_fr', 'body_en'],
    'list' => [
        'columns' => ['title_fr', 'status', 'publish_at', 'updated_at'],
        'search' => ['title_fr', 'title_en', 'slug_fr'],
        'sort' => ['title_fr', 'status', 'publish_at', 'updated_at'],
        'default_sort' => ['updated_at', 'desc'],
        'filters' => ['status' => 'Statut'],
        'per_page' => 20,
    ],
    'before_save' => static function (array $v, ?array $ex, Context $ctx) use ($uniqueSlug): array {
        $errors = [];
        $id = (int) ($ex['id'] ?? 0);

        if (($v['title_en'] ?? '') === '') {
            $v['title_en'] = $v['title_fr'];
        }
        $explicit = ['fr' => ($v['slug_fr'] ?? '') !== '', 'en' => ($v['slug_en'] ?? '') !== ''];
        $v['slug_fr'] = $explicit['fr'] ? $v['slug_fr'] : Slug::make((string) $v['title_fr']);
        $v['slug_en'] = $explicit['en'] ? $v['slug_en'] : Slug::make((string) $v['title_en']);
        if ($v['slug_en'] === $v['slug_fr']) {
            $v['slug_en'] .= '-en';
            $explicit['en'] = false;
        }
        foreach (['fr', 'en'] as $lang) {
            $key = 'slug_' . $lang;
            if (!Slug::isValid((string) $v[$key])) {
                $errors[$key] = 'Adresse invalide : minuscules, chiffres et tirets uniquement.';
                continue;
            }
            if ($explicit[$lang]) {
                if (Store::count('SELECT COUNT(*) FROM {t:posts} WHERE ' . $key . ' = ? AND id <> ?', [$v[$key], $id]) > 0) {
                    $errors[$key] = 'Cette adresse est déjà utilisée par un autre article.';
                }
            } else {
                $v[$key] = $uniqueSlug($key, (string) $v[$key], $id);
            }
        }

        if ($v['status'] === 'scheduled' && empty($v['publish_at'])) {
            $errors['publish_at'] = 'Indiquez la date de publication d\'un article programmé.';
        }
        if ($v['status'] === 'published' && empty($v['publish_at'])) {
            $v['publish_at'] = Time::nowUtc();
        }
        if (!empty($v['cover'])) {
            foreach (['cover_alt_fr' => 'français', 'cover_alt_en' => 'anglais'] as $k => $lang) {
                if (($v[$k] ?? '') === '') {
                    $errors[$k] = "Texte alternatif (« $lang ») obligatoire quand une image de couverture est présente.";
                }
            }
        }
        if ($ex === null) {
            $v['author_id'] = $ctx->userId() ?: null;
        }
        return [$v, $errors];
    },
];
