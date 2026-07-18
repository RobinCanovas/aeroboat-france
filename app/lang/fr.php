<?php
declare(strict_types=1);

/**
 * Textes de l’interface, français (langue de référence). Parité exacte avec en.php (tests/lang_parity_test.php).
 *
 * Paramètres utilisés (identiques en FR et EN) :
 *   common.published_on  :date            news.page_of  :page, :pages
 *   footer.copyright     :year            join|contact|rights.consent_html  :privacy_url (URL absolue de la page confidentialité)
 * Typographie : espaces insécables avant ; : ! ? et dans les guillemets « », apostrophes typographiques.
 */
return [
    'meta' => [
        'site_name' => 'Aéroboat France',
        'tagline' => 'Des maquettes d’aéronefs à effet de sol, conçues par des étudiants',
        'default_description' => 'Aéroboat France, association étudiante toulousaine : conception, construction et essais de maquettes d’aéronefs à effet de sol. Découvrez le projet V6.',
        'test_banner' => 'Version de test du site : elle n’est pas référencée par les moteurs de recherche et son contenu peut changer.',
        'title_suffix' => ' | Aéroboat France',
    ],

    'nav' => [
        'home' => 'Accueil',
        'project' => 'Le projet',
        'team' => 'L’équipe',
        'partners' => 'Partenaires',
        'news' => 'Actualités',
        'join' => 'Nous rejoindre',
        'contact' => 'Contact',
        'skip_to_content' => 'Aller au contenu principal',
        'menu_open' => 'Ouvrir le menu',
        'menu_close' => 'Fermer le menu',
        'language_label' => 'Langue',
        'main_label' => 'Navigation principale',
        'lang_fr' => 'Français',
        'lang_en' => 'English',
    ],

    'common' => [
        'read_more' => 'Lire la suite',
        'back' => 'Retour',
        'send' => 'Envoyer',
        'loading' => 'Chargement…',
        'all' => 'Tout',
        'published_on' => 'Publié le :date',
        'status' => [
            'done' => 'Terminé',
            'current' => 'En cours',
            'planned' => 'À venir',
        ],
        'close' => 'Fermer',
        'prev' => 'Précédent',
        'next' => 'Suivant',
        'zoom' => 'Agrandir l’image',
    ],

    'home' => [
        'hero' => [
            'badge' => 'Prototype V6 en conception',
            'title' => 'Des aéronefs à effet de sol, conçus et testés par des étudiants',
            'lead' => 'Depuis six ans, Aéroboat France développe des prototypes d’aéronefs à effet de sol. Après avoir fait voler et testé les modèles expérimentaux HV3 et HV4, nous lançons la conception de la V6.',
            'cta_primary' => 'Découvrir le projet V6',
            'cta_secondary' => 'Rejoindre l’équipe',
        ],
        'stats' => [
            ['value' => '6 ans', 'label' => 'de développement de prototypes à effet de sol'],
            ['value' => '2', 'label' => 'prototypes expérimentaux testés en vol : HV3 et HV4'],
            ['value' => '8', 'label' => 'membres dans l’équipe'],
            ['value' => '20', 'label' => 'licences 3DEXPERIENCE pour les membres'],
        ],
        'about' => [
            'kicker' => 'Qui sommes-nous ?',
            'title' => 'Une équipe d’étudiants passionnés d’aéronautique',
            'text' => 'Aéroboat France est une équipe composée principalement d’étudiants, passionnés d’aéronautique, de maquettisme et de science de l’effet de sol. Nous concevons, construisons et testons des maquettes de grande envergure pour approfondir nos connaissances par la pratique.',
        ],
        'cards' => [
            ['title' => 'Performance aérodynamique', 'text' => 'Optimiser la géométrie de l’aile (allongement, dièdre, flèche) et explorer de nouvelles configurations : winglets orientés vers le bas, flotteurs intégrés.'],
            ['title' => 'Polyvalence', 'text' => 'Concevoir un modèle capable de voler efficacement dans et hors de l’effet de sol, pour valider nos calculs et élargir son domaine d’utilisation.'],
            ['title' => 'Fiabilité et simplicité', 'text' => 'Garantir un montage rapide, en moins de 30 minutes, des liaisons robustes et une meilleure rigidité d’ensemble.'],
            ['title' => 'Flottabilité optimisée', 'text' => 'Conserver une densité globale inférieure à celle de l’eau grâce à des mousses étanches, avec une ligne de flottaison basse même dans les vagues.'],
        ],
        'timeline' => [
            'title' => 'Le chemin parcouru',
            'lead' => 'Chaque prototype nous a appris quelque chose. La V6 est pensée comme leur synthèse.',
            'items' => [
                ['date' => 'Étape 1', 'title' => 'HV3', 'text' => 'Un premier modèle qui nous a appris l’importance d’une structure rigide et de flotteurs correctement dimensionnés.', 'status' => 'done'],
                ['date' => 'Étape 2', 'title' => 'HV4', 'text' => 'Plusieurs solutions validées (forme de l’empennage, flottabilité, facilité d’assemblage) et des limites identifiées : étanchéité, rigidité de l’aile, capacité d’emport.', 'status' => 'done'],
                ['date' => 'Étape 3', 'title' => 'V6 : conception', 'text' => 'Une synthèse des acquis qui corrige les défauts passés et ouvre la voie à de nouvelles innovations.', 'status' => 'current'],
                ['date' => 'Étape 4', 'title' => 'V6 : construction et essais', 'text' => 'Valider nos calculs et élargir le domaine d’utilisation du modèle.', 'status' => 'planned'],
            ],
        ],
        'partners' => [
            'title' => 'Ils nous soutiennent',
            'lead' => 'Le projet avance grâce à l’engagement de ses partenaires.',
            'cta' => 'Voir les partenaires',
        ],
        'news' => [
            'title' => 'Actualités',
            'empty' => 'Aucune actualité pour le moment. Revenez bientôt !',
            'cta' => 'Toutes les actualités',
        ],
        'cta' => [
            'title' => 'Envie de rejoindre l’aventure ?',
            'text' => 'Vous êtes passionné d’aéronautique, de modélisme ou d’effet de sol ? Parlez-nous de vous.',
            'button' => 'Nous rejoindre',
        ],
    ],

    'project' => [
        'hero' => [
            'kicker' => 'Projet V6',
            'title' => 'Vers une nouvelle génération d’engins à effet de sol',
            'lead' => 'Depuis six ans, Aéroboat France développe des prototypes d’aéronefs à effet de sol. Après avoir fait voler et testé avec succès des modèles expérimentaux (HV3 et HV4), nous lançons le développement de la V6, un modèle pensé comme une véritable étape dans notre parcours.',
        ],
        'why' => [
            'title' => 'Pourquoi une V6 ?',
            'text' => 'Chaque prototype construit a été pour nous une occasion d’apprendre. L’objectif de la V6 est simple à énoncer, mais ambitieux : construire un engin à effet de sol fiable, performant et modulaire, capable de repousser les limites actuelles de la technologie de l’effet de sol.',
            'v3' => 'La HV3 nous a appris l’importance d’une structure rigide et de flotteurs correctement dimensionnés.',
            'v4' => 'La HV4 a validé plusieurs solutions techniques (forme de l’empennage, flottabilité, facilité d’assemblage), mais elle a aussi révélé certaines limites, notamment en matière d’étanchéité, de rigidité de l’aile et de capacité d’emport.',
            'v6' => 'La V6 est donc pensée comme une synthèse : elle s’appuie sur les réussites passées, corrige les défauts précédents et ouvre la voie à de nouvelles innovations.',
        ],
        'objectives' => [
            'title' => 'Nos objectifs',
            'items' => [
                ['title' => 'Performance aérodynamique', 'text' => 'Optimiser la géométrie de l’aile (allongement, dièdre, flèche) et explorer de nouvelles configurations : winglets orientés vers le bas, flotteurs intégrés.'],
                ['title' => 'Polyvalence', 'text' => 'Concevoir un modèle capable de voler efficacement dans et hors de l’effet de sol, afin de valider nos calculs et d’élargir son domaine d’utilisation.'],
                ['title' => 'Fiabilité et simplicité', 'text' => 'Garantir un montage rapide (moins de 30 minutes), des liaisons robustes et une meilleure rigidité d’ensemble.'],
                ['title' => 'Flottabilité optimisée', 'text' => 'Conserver une densité globale inférieure à celle de l’eau grâce à des mousses étanches, et assurer une ligne de flottaison basse même en présence de vagues.'],
                ['title' => 'Charge utile accrue', 'text' => 'Augmenter la capacité d’emport tout en maintenant une masse à vide inférieure à 2 kg : un véritable défi de conception légère.'],
            ],
        ],
        'v6' => [
            'title' => 'Les innovations de la V6',
            'lead' => 'Les principaux choix de conception retenus à ce stade du projet.',
            'items' => [
                ['title' => 'Ailes basses', 'text' => 'Des ailes de 100 cm de chaque côté, avec une corde de 50 cm, pour maximiser l’effet de sol tout en conservant une bonne stabilité.'],
                ['title' => 'Empennage en T en carbone', 'text' => 'Conçu pour rester rigide tout en évitant les perturbations du sillage de l’aile.'],
                ['title' => 'Mousses étanches', 'text' => 'Une densité globale inférieure à celle de l’eau, pour une ligne de flottaison basse même dans les vagues.'],
                ['title' => 'Train d’atterrissage amovible', 'text' => 'Étudié pour préserver l’étanchéité.'],
                ['title' => 'Étanchéité renforcée', 'text' => 'Des jonctions d’aile et un cockpit électronique protégés par des solutions d’inspiration nautique.'],
                ['title' => 'Flotteurs carénés', 'text' => 'Pour améliorer l’hydrodynamique et réduire les projections d’eau, avec des cavités internes qui évitent tout risque de naufrage.'],
                ['title' => 'Incidence réglable en vol', 'text' => 'Une piste d’innovation qui permettrait d’adapter la portance aux différentes phases de vol.'],
            ],
            'note_html' => 'La V6 est <strong>en cours de conception</strong> : ces choix peuvent encore évoluer.',
        ],
        'electronics' => [
            'title' => 'Une électronique avancée',
            'text' => 'La V6 repose sur une architecture à double calculateur, avec enregistrements automatiques sur carte SD. Nous étudions aussi l’utilisation de moteurs pour manœuvrer à l’arrêt.',
        ],
        'timeline' => [
            'title' => 'Les étapes du projet',
            'items' => [
                ['date' => 'Étape 1', 'title' => 'HV3', 'text' => 'Premier prototype expérimental, testé en vol. Il a mis en évidence l’importance d’une structure rigide et de flotteurs bien dimensionnés.', 'status' => 'done'],
                ['date' => 'Étape 2', 'title' => 'HV4', 'text' => 'Deuxième prototype expérimental, testé en vol. Il a validé plusieurs solutions et révélé des limites à corriger.', 'status' => 'done'],
                ['date' => 'Étape 3', 'title' => 'V6 : conception', 'text' => 'Définition de la nouvelle génération de maquettes, en synthèse des acquis des prototypes précédents.', 'status' => 'current'],
                ['date' => 'Étape 4', 'title' => 'V6 : construction et essais', 'text' => 'Mise en œuvre et validation de nos calculs, pour élargir le domaine d’utilisation du modèle.', 'status' => 'planned'],
            ],
        ],
        'gallery' => [
            'title' => 'Galerie',
            'lead' => 'Essais sur l’eau, construction, simulations numériques et rendus 3D : le projet en images.',
        ],
    ],

    'team' => [
        'hero' => [
            'title' => 'L’équipe',
            'lead' => 'Celles et ceux qui conçoivent, construisent et testent les maquettes d’Aéroboat France.',
        ],
        'who' => [
            'title' => 'Qui sommes-nous ?',
            'text' => 'Nous sommes une équipe composée principalement d’étudiants, passionnés d’aéronautique, de maquettisme et de science de l’effet de sol. Nous concevons, construisons et testons des maquettes de grande envergure pour approfondir nos connaissances par la pratique.',
        ],
        'role_default' => 'Membre',
        'photo_note' => 'Chaque photo est publiée avec l’accord de la personne. Pour la modifier ou la retirer, écrivez-nous ou utilisez la page « Exercer mes droits ».',
    ],

    'partners' => [
        'hero' => [
            'title' => 'Partenaires',
            'lead' => 'Ils soutiennent le projet Aéroboat France.',
        ],
        'categories' => [
            'school' => 'Établissement',
            'technology' => 'Technologie',
            'support' => 'Soutien',
        ],
        'ionis' => [
            'title' => 'IONIS Education Group',
            'text' => 'Bourse IONIS : soutien de IONIS Education Group au projet',
            'badge' => 'Partenaire école',
        ],
        'dx' => [
            'title' => '3DEXPERIENCE',
            'text' => 'Nous remercions chaleureusement 3DEXPERIENCE pour son soutien depuis 4 ans. Grâce à son engagement, nous bénéficions chaque année d’un accompagnement technique précieux et, en novembre, de licences pour tous les membres de l’association qui le souhaitent (20 licences disponibles). Ce soutien continu nous permet de réaliser nos projets avec des outils de niveau professionnel et de garantir la qualité de nos développements.',
            'licenses_label' => 'Licences pour les membres',
            'years_label' => 'Années de soutien',
            'others_label' => '3DEXPERIENCE soutient aussi d’autres associations.',
        ],
        'become' => [
            'title' => 'Devenir partenaire',
            'text' => 'Vous souhaitez soutenir un projet étudiant d’aéronautique ? Écrivez-nous, nous serons ravis d’en discuter.',
            'button' => 'Nous contacter',
        ],
        'visit' => 'Visiter le site',
    ],

    'news' => [
        'hero' => [
            'title' => 'Actualités',
            'lead' => 'Les nouvelles du projet et de l’équipe.',
        ],
        'empty' => 'Aucun article n’a encore été publié.',
        'by' => 'Par',
        'back' => 'Retour aux actualités',
        'page_of' => 'Page :page sur :pages',
    ],

    'join' => [
        'hero' => [
            'title' => 'Nous rejoindre',
            'lead' => 'Vous êtes étudiant ou passionné d’aéronautique, de modélisme ou d’effet de sol ? Dites-nous qui vous êtes et ce que vous aimeriez apporter au projet.',
        ],
        'fields' => [
            'first_name' => 'Prénom',
            'last_name' => 'Nom',
            'email' => 'Adresse e-mail',
            'school' => 'École ou établissement',
            'level' => 'Niveau d’études',
            'skills' => 'Compétences',
            'motivation' => 'Motivation',
            'availability' => 'Disponibilités',
            'cv' => 'CV (PDF, facultatif)',
        ],
        'hints' => [
            'cv' => 'Fichier PDF uniquement, 5 Mo maximum. Il est stocké hors de l’espace public du site.',
            'motivation' => 'Quelques lignes suffisent : ce qui vous attire dans le projet et ce que vous aimeriez y apporter.',
        ],
        'consent_html' => 'J’accepte qu’Aéroboat France utilise ces informations pour étudier ma candidature et me répondre. En savoir plus dans la <a href=":privacy_url">politique de confidentialité</a>.',
        'submit' => 'Envoyer ma candidature',
        'sent' => [
            'title' => 'Candidature envoyée',
            'text' => 'Merci ! Nous avons bien reçu votre candidature et reviendrons vers vous par e-mail.',
        ],
        'steps' => [
            ['title' => 'Remplissez le formulaire', 'text' => 'Présentez-vous et expliquez ce qui vous motive. Le CV est facultatif.'],
            ['title' => 'Nous étudions votre candidature', 'text' => 'L’équipe lit chaque candidature avec attention.'],
            ['title' => 'Nous vous répondons', 'text' => 'Nous revenons vers vous par e-mail à l’adresse indiquée.'],
        ],
    ],

    'contact' => [
        'hero' => [
            'title' => 'Contact',
            'lead' => 'Une question, une proposition de partenariat ou une demande de presse ? Écrivez-nous.',
        ],
        'fields' => [
            'name' => 'Nom',
            'email' => 'Adresse e-mail',
            'subject' => 'Objet',
            'body' => 'Message',
        ],
        'consent_html' => 'J’accepte qu’Aéroboat France utilise ces informations pour répondre à mon message. En savoir plus dans la <a href=":privacy_url">politique de confidentialité</a>.',
        'submit' => 'Envoyer le message',
        'sent' => [
            'title' => 'Message envoyé',
            'text' => 'Merci ! Nous avons bien reçu votre message et vous répondrons dès que possible.',
        ],
        'info' => [
            'title' => 'Coordonnées',
            'email_label' => 'E-mail',
            'phone_label' => 'Téléphone',
            'city_label' => 'Ville',
        ],
    ],

    'rights' => [
        'hero' => [
            'title' => 'Exercer mes droits',
            'lead' => 'Accès, rectification, effacement, opposition, portabilité : utilisez ce formulaire pour exercer vos droits sur vos données personnelles.',
        ],
        'fields' => [
            'name' => 'Nom',
            'email' => 'Adresse e-mail',
            'request_type' => 'Nature de la demande',
            'body' => 'Votre demande',
        ],
        'types' => [
            'access' => 'Accéder à mes données',
            'rectify' => 'Rectifier mes données',
            'erase' => 'Effacer mes données',
            'object' => 'M’opposer à un traitement',
            'portability' => 'Récupérer mes données (portabilité)',
        ],
        'consent_html' => 'J’accepte qu’Aéroboat France utilise ces informations pour traiter ma demande. En savoir plus dans la <a href=":privacy_url">politique de confidentialité</a>.',
        'submit' => 'Envoyer ma demande',
        'sent' => [
            'title' => 'Demande envoyée',
            'text' => 'Merci ! Nous avons bien reçu votre demande et y répondrons dans un délai d’un mois maximum.',
        ],
        'delay_note' => 'Nous répondons dans un délai d’un mois maximum. En cas de doute raisonnable sur votre identité, nous pouvons vous demander de la confirmer.',
    ],

    'forms' => [
        'errors' => [
            'required' => 'Ce champ est obligatoire.',
            'email' => 'Veuillez saisir une adresse e-mail valide.',
            'too_long' => 'Ce texte est trop long.',
            'consent' => 'Vous devez cocher la case de consentement pour envoyer le formulaire.',
            'too_fast' => 'Le formulaire a été envoyé trop rapidement. Patientez quelques secondes, puis réessayez.',
            'rate_limit' => 'Trop d’envois depuis votre connexion. Réessayez dans une heure.',
            'csrf' => 'Le formulaire a expiré. Rechargez la page, puis réessayez.',
            'pdf_invalid' => 'Le fichier doit être un PDF valide.',
            'pdf_too_large' => 'Le PDF dépasse la taille maximale de 5 Mo.',
            'image_invalid' => 'Ce fichier n’est pas une image valide (JPEG, PNG ou WebP).',
            'image_too_large' => 'L’image dépasse la taille maximale autorisée.',
            'generic' => 'Une erreur est survenue. Veuillez réessayer plus tard.',
        ],
        'honeypot_label' => 'Ne pas remplir ce champ',
    ],

    'footer' => [
        'about' => [
            'title' => 'Aéroboat France',
            'text' => 'Association étudiante toulousaine : conception, construction et essais de maquettes d’aéronefs à effet de sol.',
        ],
        'nav' => [
            'title' => 'Navigation',
        ],
        'legal' => [
            'title' => 'Informations légales',
            'legal' => 'Mentions légales',
            'privacy' => 'Confidentialité',
            'cookies' => 'Cookies',
            'rights' => 'Exercer mes droits',
        ],
        'contact' => [
            'title' => 'Contact',
            'text' => 'Une question, une envie de nous rejoindre ou de nous soutenir ? Écrivez-nous.',
        ],
        'social' => [
            'title' => 'Suivez-nous',
            'instagram' => 'Instagram',
            'linkedin' => 'LinkedIn',
        ],
        'copyright' => '© :year Aéroboat France. Tous droits réservés.',
        'no_tracking' => 'Aucun traceur, aucun cookie pour les visiteurs.',
    ],

    'legal' => [
        'title' => 'Mentions légales',
        'lead' => 'Informations légales relatives à ce site, conformément à la loi pour la confiance dans l’économie numérique.',
        'publisher' => [
            'title' => 'Éditeur du site',
            'name_label' => 'Nom',
            'legal_form_label' => 'Forme juridique',
            'rna_label' => 'Numéro RNA',
            'address_label' => 'Adresse',
            'city_label' => 'Ville',
            'email_label' => 'E-mail',
            'phone_label' => 'Téléphone',
            'director_label' => 'Directeur ou directrice de la publication',
        ],
        'host' => [
            'title' => 'Hébergeur',
            'name_label' => 'Société',
            'address_label' => 'Adresse',
        ],
        'ip' => [
            'title' => 'Propriété intellectuelle',
            'text' => 'Les textes, images, logos, schémas et visuels présents sur ce site sont la propriété d’Aéroboat France ou de leurs auteurs et partenaires respectifs. Toute reproduction ou utilisation sans autorisation préalable est interdite, sauf mention contraire. Les marques et logos des partenaires appartiennent à leurs propriétaires.',
        ],
        'liability' => [
            'title' => 'Responsabilité',
            'text' => 'Aéroboat France s’efforce de publier des informations exactes et à jour, mais ne garantit pas l’absence d’erreurs ou d’omissions. Le projet V6 est en cours de conception : les caractéristiques présentées peuvent évoluer. Les liens vers des sites tiers sont fournis à titre d’information ; Aéroboat France n’est pas responsable de leur contenu.',
        ],
        'data' => [
            'title' => 'Données personnelles',
            'text' => 'Le traitement des données personnelles est détaillé dans la politique de confidentialité. Vous pouvez exercer vos droits à tout moment.',
            'privacy_link' => 'Lire la politique de confidentialité',
            'rights_link' => 'Exercer mes droits',
        ],
    ],

    'privacy' => [
        'title' => 'Politique de confidentialité',
        'lead' => 'Nous collectons le strict nécessaire, uniquement lorsque vous nous écrivez, et nous ne traçons pas vos visites.',
        'controller' => [
            'title' => 'Responsable du traitement',
            'text' => 'Le responsable du traitement est l’association Aéroboat France, dont les coordonnées figurent ci-dessous et dans les mentions légales.',
            'email_label' => 'E-mail',
            'phone_label' => 'Téléphone',
            'address_label' => 'Adresse',
        ],
        'forms' => [
            'title' => 'Données collectées et finalités',
            'intro' => 'Aucune donnée personnelle n’est collectée lors de la simple consultation du site. Des données ne sont enregistrées que si vous remplissez l’un des formulaires ci-dessous.',
            'join' => [
                'title' => 'Formulaire « Nous rejoindre »',
                'text' => 'Prénom, nom, adresse e-mail, école ou établissement, niveau d’études, compétences, motivation, disponibilités et, si vous le souhaitez, votre CV au format PDF. Finalité : étudier votre candidature et vous répondre.',
            ],
            'contact' => [
                'title' => 'Formulaire « Contact »',
                'text' => 'Nom, adresse e-mail, objet et contenu du message. Finalité : répondre à votre message.',
            ],
            'rights' => [
                'title' => 'Formulaire « Exercer mes droits »',
                'text' => 'Nom, adresse e-mail, nature et contenu de votre demande. Finalité : traiter votre demande d’exercice de droits.',
            ],
            'members' => [
                'title' => 'Espace membres',
                'text' => 'Les comptes de l’espace membres sont créés par le bureau de l’association. Ils comportent un identifiant, un nom d’affichage, éventuellement une adresse e-mail, et un mot de passe conservé uniquement sous forme chiffrée (hachée). Finalité : permettre aux membres autorisés de gérer le site.',
            ],
            'security' => [
                'title' => 'Limitation du nombre d’envois',
                'text' => 'Pour éviter les abus, le nombre d’envois de formulaires est limité. Votre adresse IP n’est jamais conservée en clair : seule une empreinte irréversible, calculée avec une clé secrète, est enregistrée, pendant 24 heures au plus.',
            ],
        ],
        'basis' => [
            'title' => 'Base légale',
            'text' => 'Les formulaires de candidature et de contact reposent sur votre consentement, que vous exprimez en cochant la case prévue à cet effet. Le traitement des demandes d’exercice de droits répond à une obligation légale. La limitation du nombre d’envois repose sur notre intérêt légitime à protéger le site contre les abus.',
        ],
        'retention' => [
            'title' => 'Durées de conservation',
            'messages' => 'Messages de contact et demandes d’exercice de droits : 12 mois.',
            'applications' => 'Candidatures : 24 mois.',
            'cv' => 'CV : conservé aussi longtemps que la candidature correspondante (24 mois), dans un dossier non accessible depuis internet.',
            'throttle' => 'Empreinte d’adresse IP pour la limitation des envois : 24 heures au plus.',
            'after' => 'À l’issue de ces durées, les données sont supprimées. Vous pouvez demander leur suppression avant l’échéance.',
        ],
        'recipients' => [
            'title' => 'Destinataires',
            'text' => 'Vos données sont destinées aux membres du bureau et de l’équipe en charge du traitement de votre demande. Elles ne sont ni vendues ni cédées à des tiers. Un message peut être transmis par e-mail à l’adresse de contact de l’association pour nous prévenir de son arrivée.',
        ],
        'hosting' => [
            'title' => 'Hébergement et sécurité',
            'text' => 'Le site et sa base de données sont hébergés par OVH. Les formulaires sont protégés contre les envois automatisés sans traceur ni cookie, et les CV sont stockés hors de l’espace public du serveur. L’hébergeur peut tenir des journaux techniques de connexion, nécessaires au fonctionnement et à la sécurité du service.',
        ],
        'tracking' => [
            'title' => 'Aucun traceur',
            'text' => 'Ce site n’utilise ni outil de mesure d’audience, ni publicité, ni bouton de réseau social intégré, ni service externe de polices ou de contenus. Aucun cookie n’est déposé pour les visiteurs. Pour plus de détails, consultez la page cookies.',
            'cookies_link' => 'Page cookies',
        ],
        'rights' => [
            'title' => 'Vos droits',
            'text_html' => 'Conformément au RGPD et à la loi Informatique et Libertés, vous disposez d’un droit d’<strong>accès</strong>, de <strong>rectification</strong>, d’<strong>effacement</strong>, de <strong>limitation</strong> et d’<strong>opposition</strong>, ainsi que d’un droit à la <strong>portabilité</strong> de vos données. Lorsque le traitement repose sur votre consentement, vous pouvez le retirer à tout moment. Nous répondons dans un délai d’un mois maximum.',
            'cta' => 'Exercer mes droits avec le formulaire dédié',
        ],
        'complaint' => [
            'title' => 'Réclamation',
            'text_html' => 'Si vous estimez que vos droits ne sont pas respectés, vous pouvez introduire une réclamation auprès de la Commission nationale de l’informatique et des libertés (CNIL), 3 place de Fontenoy, TSA 80715, 75334 Paris Cedex 07, <a href="https://www.cnil.fr">www.cnil.fr</a>.',
        ],
        'contact' => [
            'title' => 'Nous contacter',
            'text' => 'Pour toute question relative à vos données personnelles, écrivez-nous à l’adresse ci-dessous ou utilisez le formulaire d’exercice des droits.',
        ],
    ],

    'cookies' => [
        'title' => 'Cookies',
        'lead' => 'Aucun cookie pour les visiteurs ; un cookie de session strictement nécessaire est réservé à l’espace membres.',
        'visitors' => [
            'title' => 'Visiteurs du site',
            'text' => 'Aucun cookie n’est déposé sur votre appareil lorsque vous consultez le site ou remplissez un formulaire. Les formulaires sont protégés par un jeton signé qui ne repose sur aucun cookie. Nous n’utilisons aucun outil de mesure d’audience ni de publicité, et aucune ressource n’est chargée depuis un service externe : les polices sont hébergées sur le site lui-même.',
        ],
        'members' => [
            'title' => 'Espace membres',
            'text' => 'Lorsqu’un membre autorisé se connecte à l’espace membres, un cookie de session strictement nécessaire est déposé pour maintenir sa connexion. Il ne sert à aucune autre fin et n’est jamais déposé pour les visiteurs. Ce type de cookie est exempté de consentement.',
            'name_label' => 'Nom du cookie',
            'name_value' => 'abf_admin',
            'purpose_label' => 'Finalité',
            'purpose_value' => 'Maintenir la session de connexion à l’espace membres',
            'duration_label' => 'Durée',
            'duration_value' => 'Durée de la session',
        ],
        'banner' => [
            'title' => 'Pas de bannière de consentement',
            'text' => 'Comme aucun cookie non essentiel n’est utilisé, ce site n’affiche pas de bannière de consentement.',
        ],
        'more' => [
            'title' => 'En savoir plus',
            'text' => 'Pour en savoir plus sur le traitement de vos données, consultez la politique de confidentialité.',
            'privacy_link' => 'Politique de confidentialité',
        ],
    ],

    'errors' => [
        '404' => [
            'title' => 'Page introuvable',
            'text' => 'La page demandée n’existe pas ou a été déplacée.',
            'cta' => 'Retour à l’accueil',
        ],
        '500' => [
            'title' => 'Erreur du serveur',
            'text' => 'Un incident est survenu de notre côté. Veuillez réessayer dans quelques instants.',
        ],
        '403' => [
            'title' => 'Accès refusé',
            'text' => 'Vous n’avez pas l’autorisation d’accéder à cette page.',
        ],
    ],

    'admin_public' => [
        'restricted' => 'Espace réservé aux membres de l’association.',
        'link' => 'Espace membres',
    ],

    'img' => [
        'lake-alpine' => 'Image de synthèse d’une maquette blanche à effet de sol sur un lac de montagne, avec des sommets en arrière-plan',
        'water-run' => 'Maquette rouge à hélice flottant sur l’eau d’un lac, vue de trois quarts',
        'water-skim' => 'Maquette rouge filant sur l’eau en soulevant des gerbes d’écume',
        'hold-red' => 'Une personne tient à bout de bras une maquette rouge de grande envergure, dans un parc',
        'lakeside-duo' => 'Deux personnes portent une maquette rouge au bord d’un lac',
        'ribs-ground' => 'Structure à nervures apparentes d’une aile en construction, posée sur un dallage, avec des extrémités rouges',
        'dusk-ground' => 'Structure à nervures d’une maquette posée au sol au crépuscule, entourée d’arbres',
        'dusk-lake' => 'Maquette sombre sur un lac au crépuscule, avec un kayakiste en silhouette en arrière-plan',
        'bench-build' => 'Atelier : fuselage rouge sur un établi avec câblage, hélice et sac de protection pour batteries',
        'wing-closeup' => 'Gros plan sur la structure en bois d’une aile posée au sol, avec des arbres en arrière-plan',
        'wing-pavement' => 'Aile à nervures apparentes posée sur un dallage, avec des dérives rouges aux extrémités',
        'cfd-streams' => 'Simulation numérique : filets d’air colorés, du bleu au jaune, autour d’une forme d’aile',
        'cfd-color' => 'Simulation numérique d’une maquette à empennage en T dont la surface est colorée du bleu au vert',
        'render-dark' => 'Rendu 3D sur fond sombre d’une maquette rouge et bois, avec ailes à nervures et empennage en T',
        'render-white' => 'Rendu 3D d’une maquette blanche à aile basse et empennage en T, avec deux hélices',
        'render-blue' => 'Rendu 3D d’une maquette blanche à aile basse bleue et empennage en T, avec hélices',
        'team-build' => 'Deux personnes, accroupies sur un pavage, assemblent une maquette à revêtement bleu',
        'cgi-v-red' => 'Image de synthèse d’un aéronef rouge à longues ailes en bois, avec empennage en T et cockpit vitré',
        'model-water' => 'Maquette rouge à hélice glissant sur l’eau, vue de face au ras de la surface',
        'ribs-lawn' => 'Grande maquette à aile en bois, avec dérives rouges, posée sur un dallage le long d’une pelouse',
        'event-stand' => 'Trois personnes sur un stand de salon, avec une maquette bleue suspendue devant elles',
        'team-clement' => 'Portrait de Clément Thibault',
        'team-joan' => 'Portrait de Joan Le Pouhaër',
        'team-pierre' => 'Portrait de Pierre Stamm',
        'team-kerian' => 'Portrait de Kerian Fiter',
        'team-mathis' => 'Portrait de Mathis Wojtkowski',
        'team-benjamin' => 'Portrait de Benjamin Canovas',
        'team-chislain' => 'Portrait de Chislain Jaures Kamdem Kam',
        'team-robin' => 'Portrait de Robin Canovas',
        'logo' => 'Logo d’Aéroboat France : un cercle portant le nom, avec la silhouette d’un aéronef à empennage en T entre deux volutes, l’une bleue, l’autre rouge',
        'logo-white' => 'Logo d’Aéroboat France en blanc : un cercle portant le nom, avec la silhouette d’un aéronef à empennage en T entre deux volutes, l’une bleue, l’autre rouge',
        'partner-3dx' => 'Logo 3DEXPERIENCE',
        'partner-ionis' => 'Logo IONIS Education Group',
        'partner-uncb' => 'Logo de l’association « Un morceau de ciel bleu » : un cadran d’horizon artificiel avec un avion en vol',
    ],
];
