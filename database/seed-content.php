<?php
declare(strict_types=1);

/**
 * Contenu initial du site (lu par database/seed.php, idempotent).
 * Retourne ['team' => [...], 'partners' => [...], 'posts' => [...]] ; les clés des lignes = colonnes de 001_init.sql
 * (sauf `id`). Dates en UTC `Y-m-d H:i:s` : seule la date de création du seed est utilisée, aucune date inventée.
 *
 * Source des faits : docs/source/wp-pages.json (site actuel). Voir docs/CONTENT-REPORT.md pour les points à confirmer.
 * `photo` et `logo` = nom de base de l’image dans public/img/manifest.json (sans taille ni extension).
 */

$now = gmdate('Y-m-d H:i:s');

/** Membres : noms, dans l’ordre et la graphie de la page « Équipe » actuelle. Aucun rôle n’est publié dans les sources. */
$members = [
    ['Clément THIBAULT', 'team-clement'],
    ['Joan LE POUHAËR', 'team-joan'],
    ['Pierre STAMM', 'team-pierre'],
    ['Kerian FITER', 'team-kerian'],
    ['Mathis WOJTKOWSKI', 'team-mathis'],
    ['Benjamin CANOVAS', 'team-benjamin'],
    ['Chislain Jaures KAMDEM KAM', 'team-chislain'],
    ['Robin CANOVAS', 'team-robin'],
];

$team = [];
foreach ($members as $i => [$name, $photo]) {
    $team[] = [
        'name' => $name,
        'role_fr' => null, // repli sur team.role_default (« Membre »)
        'role_en' => null, // fallback to team.role_default (“Member”)
        'bio_fr' => null,
        'bio_en' => null,
        'photo' => $photo,
        'photo_consent' => 1, // photos déjà publiques sur le site actuel : à vérifier en admin
        'linkedin' => null,
        'sort_order' => ($i + 1) * 10,
        'published' => 1,
        'created_at' => $now,
        'updated_at' => $now,
    ];
}

$partners = [
    [
        'name' => 'IONIS Education Group',
        'category' => 'school',
        'description_fr' => 'Bourse IONIS : soutien de IONIS Education Group au projet',
        'description_en' => 'IONIS Scholarship: support from IONIS Education Group for the project',
        'logo' => 'partner-ionis',
        'logo_consent' => 1,
        'url' => 'https://www.ionis-group.com/',
        'sort_order' => 10,
        'published' => 1,
        'created_at' => $now,
        'updated_at' => $now,
    ],
    [
        'name' => '3DEXPERIENCE',
        'category' => 'technology',
        'description_fr' => 'Soutien depuis 4 ans : accompagnement technique et licences pour les membres de l’association (20 licences disponibles). 3DEXPERIENCE soutient aussi d’autres associations.',
        'description_en' => 'Supporting us for 4 years: technical guidance and licences for association members (20 licences available). 3DEXPERIENCE also supports other associations.',
        'logo' => 'partner-3dx',
        'logo_consent' => 1,
        'url' => 'https://www.3ds.com/products/3dexperience',
        'sort_order' => 20,
        'published' => 1,
        'created_at' => $now,
        'updated_at' => $now,
    ],
    [
        // Fiche volontairement NON publiée (règle du projet) : à valider avec l’association avant toute mise en ligne.
        'name' => 'Un morceau de ciel bleu',
        'category' => 'support',
        'description_fr' => null,
        'description_en' => null,
        'logo' => 'partner-uncb',
        'logo_consent' => 0,
        'url' => null,
        'sort_order' => 30,
        'published' => 0,
        'created_at' => $now,
        'updated_at' => $now,
    ],
];

$bodyFr = <<<'MD'
Aéroboat France entre dans une nouvelle étape : nous lançons le développement de la **V6**, un modèle pensé comme une véritable étape dans notre parcours.

Après plusieurs années de développement de prototypes d’aéronefs à effet de sol, et après avoir fait voler et testé avec succès les modèles expérimentaux HV3 et HV4, nous voulons construire un engin à effet de sol fiable, performant et modulaire, capable de repousser les limites actuelles de la technologie de l’effet de sol.

## Ce que nous ont appris les prototypes

- La HV3 nous a appris l’importance d’une structure rigide et de flotteurs correctement dimensionnés.
- La HV4 a validé plusieurs solutions techniques (forme de l’empennage, flottabilité, facilité d’assemblage), mais elle a aussi révélé des limites en matière d’étanchéité, de rigidité de l’aile et de capacité d’emport.

La V6 est pensée comme une synthèse : elle s’appuie sur les réussites passées, corrige les défauts précédents et ouvre la voie à de nouvelles innovations.

## Ce que nous visons

- Optimiser la géométrie de l’aile et explorer de nouvelles configurations.
- Voler efficacement dans et hors de l’effet de sol.
- Garantir un montage rapide (moins de 30 minutes) et une meilleure rigidité d’ensemble.
- Conserver une densité globale inférieure à celle de l’eau grâce à des mousses étanches.
- Augmenter la capacité d’emport tout en maintenant une masse à vide inférieure à 2 kg.

## Les choix de conception à ce stade

Des ailes basses, un empennage en T en carbone, un train d’atterrissage amovible, une étanchéité renforcée par des solutions d’inspiration nautique, des flotteurs carénés à cavités internes et une incidence réglable en vol, qui reste une piste d’innovation. Côté électronique, la V6 repose sur une architecture à double calculateur, avec enregistrements automatiques sur carte SD.

Le projet est en cours de conception : ces choix peuvent encore évoluer. Retrouvez le détail sur la page du projet, et n’hésitez pas à nous écrire si vous souhaitez nous rejoindre.
MD;

$bodyEn = <<<'MD'
Aéroboat France is entering a new stage: we are launching the development of the **V6**, a model designed as a true milestone in our journey.

After several years of developing ground-effect aircraft prototypes, and after successfully flying and testing the experimental HV3 and HV4 models, we want to build a reliable, high-performance and modular ground-effect craft capable of pushing the current limits of ground-effect technology.

## What the prototypes taught us

- The HV3 taught us the importance of a rigid structure and properly sized floats.
- The HV4 validated several technical solutions (tail design, buoyancy, ease of assembly), but it also revealed limitations regarding waterproofing, wing rigidity and payload capacity.

The V6 is conceived as a synthesis: it builds on past successes, corrects previous shortcomings and paves the way for new innovations.

## What we are aiming for

- Optimise the wing geometry and explore new configurations.
- Fly efficiently both in and out of ground effect.
- Ensure quick assembly (under 30 minutes) and improved overall stiffness.
- Keep an overall density lower than that of water by using watertight foams.
- Increase the carrying capacity while keeping the empty weight under 2 kg.

## Design choices at this stage

Low wings, a carbon-fibre T-tail, removable landing gear, enhanced watertightness through solutions inspired by nautical engineering, streamlined floats with internal cavities and an in-flight adjustable angle of incidence, which remains an innovation pathway. On the electronics side, the V6 relies on a dual-computer architecture, with automatic recordings on an SD card.

The project is still being designed: these choices may still change. Find the details on the project page, and feel free to write to us if you would like to join us.
MD;

$posts = [
    [
        'slug_fr' => 'lancement-du-projet-v6',
        'slug_en' => 'v6-project-launch',
        'title_fr' => 'Lancement du projet V6',
        'title_en' => 'Launch of the V6 project',
        'excerpt_fr' => 'Après les essais en vol des modèles HV3 et HV4, Aéroboat France lance le développement de la V6, une nouvelle génération d’engins à effet de sol.',
        'excerpt_en' => 'After flight-testing the HV3 and HV4 models, Aéroboat France is launching the development of the V6, a new generation of ground-effect craft.',
        'body_fr' => $bodyFr,
        'body_en' => $bodyEn,
        'cover' => 'water-run',
        'cover_alt_fr' => 'Maquette rouge à hélice flottant sur l’eau d’un lac, vue de trois quarts',
        'cover_alt_en' => 'Red propeller-driven model floating on a lake, seen from three-quarters',
        'status' => 'published',
        'publish_at' => $now,
        'author_id' => null,
        'created_at' => $now,
        'updated_at' => $now,
    ],
];

return [
    'team' => $team,
    'partners' => $partners,
    'posts' => $posts,
];
