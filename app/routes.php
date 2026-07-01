<?php
declare(strict_types=1);

/**
 * Table des routes publiques (contrat figé, voir docs/CONTRACTS.md §2).
 * Clé => chemins localisés (sans slash initial), contrôleur@méthode, méthodes HTTP.
 * Les chemins admin (/admin/...) et les routes non localisées (sitemap, robots) sont traités à part.
 */
return [
    'home' => [
        'fr' => '', 'en' => '',
        'action' => 'PageController@home', 'methods' => ['GET'],
    ],
    'project' => [
        'fr' => 'projet', 'en' => 'project',
        'action' => 'PageController@project', 'methods' => ['GET'],
    ],
    'team' => [
        'fr' => 'equipe', 'en' => 'team',
        'action' => 'PageController@team', 'methods' => ['GET'],
    ],
    'partners' => [
        'fr' => 'partenaires', 'en' => 'partners',
        'action' => 'PageController@partners', 'methods' => ['GET'],
    ],
    'news' => [
        'fr' => 'actualites', 'en' => 'news',
        'action' => 'NewsController@index', 'methods' => ['GET'],
    ],
    'news_show' => [
        'fr' => 'actualites/{slug}', 'en' => 'news/{slug}',
        'action' => 'NewsController@show', 'methods' => ['GET'],
    ],
    'join' => [
        'fr' => 'rejoindre', 'en' => 'join',
        'action' => 'FormController@join', 'methods' => ['GET', 'POST'],
    ],
    'contact' => [
        'fr' => 'contact', 'en' => 'contact',
        'action' => 'FormController@contact', 'methods' => ['GET', 'POST'],
    ],
    'rights' => [
        'fr' => 'exercer-mes-droits', 'en' => 'exercise-my-rights',
        'action' => 'FormController@rights', 'methods' => ['GET', 'POST'],
    ],
    'legal' => [
        'fr' => 'mentions-legales', 'en' => 'legal-notice',
        'action' => 'PageController@legal', 'methods' => ['GET'],
    ],
    'privacy' => [
        'fr' => 'confidentialite', 'en' => 'privacy',
        'action' => 'PageController@privacy', 'methods' => ['GET'],
    ],
    'cookies' => [
        'fr' => 'cookies', 'en' => 'cookies',
        'action' => 'PageController@cookies', 'methods' => ['GET'],
    ],
];
