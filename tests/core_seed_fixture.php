<?php
declare(strict_types=1);

/**
 * Jeu minimal de TEST (fictif, jamais affiché en production) : utilisé par `migrate.php --demo`
 * et les tests core_* quand database/seed-content.php (contenu-i18n) est absent.
 */
return [
    'team' => [
        ['name' => 'Membre Test', 'role_fr' => 'Rôle test', 'role_en' => 'Test role', 'photo' => null, 'published' => 1],
        ['name' => 'Karim Benali', 'role_fr' => 'Ne doit jamais être inséré', 'role_en' => 'Must never be inserted'],
    ],
    'partners' => [
        ['name' => 'Partenaire Test', 'category' => 'school', 'published' => 1],
        ['name' => 'Un morceau de ciel bleu', 'category' => 'support', 'published' => 1],
    ],
    'posts' => [
        [
            'slug_fr' => 'article-test', 'slug_en' => 'test-post',
            'title_fr' => 'Article de test', 'title_en' => 'Test post',
            'excerpt_fr' => 'Résumé de test.', 'excerpt_en' => 'Test excerpt.',
            'body_fr' => "## Titre\n\nTexte **gras** et [lien](https://example.org).", 'body_en' => "## Title\n\nSome **bold** text.",
            'status' => 'published', 'publish_at' => '2026-01-01 10:00:00',
        ],
        [
            'slug_fr' => 'article-futur', 'slug_en' => 'future-post',
            'title_fr' => 'Article programmé', 'title_en' => 'Scheduled post',
            'body_fr' => 'Futur.', 'body_en' => 'Future.',
            'status' => 'scheduled', 'publish_at' => '2099-01-01 00:00:00',
        ],
    ],
];
