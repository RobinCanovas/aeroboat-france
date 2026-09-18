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

/** Membres : noms, dans l’ordre et la graphie de la page « Équipe » actuelle. Rôles des trois fondateurs : page « Équipe » du site actuel ; les autres membres n’ont pas de titre publié. */
$members = [
    ['Clément THIBAULT', 'team-clement', 'Président et cofondateur', 'President and co-founder'],
    ['Joan LE POUHAËR', 'team-joan', 'Trésorier et cofondateur', 'Treasurer and co-founder'],
    ['Pierre STAMM', 'team-pierre', 'Secrétaire et cofondateur', 'Secretary and co-founder'],
    ['Kerian FITER', 'team-kerian'],
    ['Mathis WOJTKOWSKI', 'team-mathis'],
    ['Benjamin CANOVAS', 'team-benjamin'],
    ['Chislain Jaures KAMDEM KAM', 'team-chislain'],
    ['Robin CANOVAS', 'team-robin'],
];

$team = [];
foreach ($members as $i => $member) {
    [$name, $photo] = $member;
    $roleFr = $member[2] ?? null;
    $roleEn = $member[3] ?? null;
    $team[] = [
        'name' => $name,
        'role_fr' => $roleFr ?? null, // sinon repli sur team.role_default (« Membre »)
        'role_en' => $roleEn ?? null, // otherwise fallback to team.role_default (“Member”)
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
Aéroboat France passe à l’étape suivante : nous lançons le développement de la **V6**, un modèle pensé comme un vrai jalon dans notre parcours.

Après plusieurs années de prototypes à effet de sol, et après avoir fait voler et testé les modèles expérimentaux HV3 et HV4, nous voulons construire un engin fiable, performant et modulaire, qui repousse les limites actuelles de cette technologie.

## Ce que les prototypes nous ont appris

- La HV3 nous a montré qu’il faut une structure rigide et des flotteurs bien dimensionnés.
- La HV4 a validé plusieurs solutions (forme de l’empennage, flottabilité, facilité d’assemblage), mais elle a aussi montré ses limites sur l’étanchéité, la rigidité de l’aile et la charge utile.

La V6 est une synthèse : elle garde ce qui a marché, corrige ce qui a posé problème et nous permet d’essayer de nouvelles idées.

## Ce que nous visons

- Optimiser la géométrie de l’aile et tester de nouvelles configurations.
- Voler aussi bien dans l’effet de sol qu’en dehors.
- Monter l’ensemble en moins de 30 minutes, avec une structure plus rigide.
- Rester plus léger que l’eau grâce à des mousses étanches.
- Emporter davantage de charge, avec une masse à vide inférieure à 2 kg.

## Les choix de conception à ce stade

Des ailes basses, un empennage en T en carbone, un train d’atterrissage amovible, une étanchéité renforcée par des solutions inspirées du monde nautique, des flotteurs carénés à cavités internes et une incidence réglable en vol, qui reste pour l’instant une piste. Côté électronique, la V6 repose sur une architecture à double calculateur, avec enregistrement automatique sur carte SD, et nous étudions des moteurs pour manœuvrer à l’arrêt.

Le projet est en cours de conception et ces choix peuvent encore changer. Retrouvez le détail sur la page du projet, et écrivez-nous si vous voulez nous rejoindre.
MD;

$bodyEn = <<<'MD'
Aéroboat France is moving on to the next step: we are launching the development of the **V6**, a model designed as a real milestone in our journey.

After several years of ground-effect prototypes, and after flying and testing the experimental HV3 and HV4 models, we want to build a reliable, high-performance and modular craft that pushes the current limits of the technology.

## What the prototypes taught us

- The HV3 showed us that we need a rigid structure and properly sized floats.
- The HV4 validated several solutions (tail design, buoyancy, ease of assembly), but it also showed its limits on waterproofing, wing rigidity and payload.

The V6 is a synthesis: it keeps what worked, fixes what caused problems and lets us try new ideas.

## What we are aiming for

- Optimise the wing geometry and test new configurations.
- Fly both in and out of ground effect.
- Assemble everything in under 30 minutes, with a stiffer structure.
- Stay lighter than water thanks to watertight foams.
- Carry more payload, with an empty weight under 2 kg.

## Design choices at this stage

Low wings, a carbon-fibre T-tail, removable landing gear, watertightness improved with solutions inspired by boat building, streamlined floats with internal cavities and an in-flight adjustable angle of incidence, which is still just an idea for now. On the electronics side, the V6 relies on a dual-computer architecture with automatic recording to an SD card, and we are looking at motors to manoeuvre when stationary.

The project is still being designed and these choices may change. Find the details on the project page, and write to us if you would like to join us.
MD;

$posts = [
    [
        'slug_fr' => 'lancement-du-projet-v6',
        'slug_en' => 'v6-project-launch',
        'title_fr' => 'Lancement du projet V6',
        'title_en' => 'Launch of the V6 project',
        'excerpt_fr' => 'Après les essais en vol des modèles HV3 et HV4, nous lançons la V6, une nouvelle génération d’engins à effet de sol.',
        'excerpt_en' => 'After the flight tests of the HV3 and HV4 models, we are launching the V6, a new generation of ground-effect craft.',
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
