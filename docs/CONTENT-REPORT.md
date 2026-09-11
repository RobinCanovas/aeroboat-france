# Rapport de contenu (contenu-i18n)

Sources : `docs/source/wp-pages.json` (site actuel, daté d’octobre 2025), `Projet.odt` (autre projet, sans lien avec Aéroboat France : inutilisé), `Page1.html` (maquette avec équipe fictive et mention « IPSA Toulouse » : inutilisé), `Presentation_Aeroboat_France.pptx` (présentation du site, 9 septembre 2025 : aucun rôle de membre ; « fondée par d’anciens étudiants de l’IPSA Toulouse ou autre » est une hypothèse, non publiée).

## À actualiser (chiffres et formulations datés d’octobre 2025 ; nous sommes en novembre 2026)

| Où | Texte publié | Source / remarque |
|---|---|---|
| `home.hero.lead`, `home.stats[0]`, `project.hero.lead` | « six ans » / « 6 ans » de développement | wp-pages.json (gallery) : « For six years… ». Probablement sept ans aujourd’hui. |
| `home.stats[2]` | 8 membres | page Équipe (8 noms). À vérifier. |
| `partners.dx.text` | « depuis 4 ans » (soutien 3DEXPERIENCE) | wp-pages.json (general) : « for the past 4 years ». |
| `partners.dx.text`, `home.stats[3]` | licences « en novembre », 20 licences | même source : « in November… 20 licenses available ». Texte conservé au présent ; novembre 2026 est en cours. |
| Article de seed | « plusieurs années » (au lieu de « six ans », pour limiter le risque d’obsolescence) | — |

## À confirmer

1. **Bourse IONIS** : bloc « Bourse IONIS : soutien de IONIS Education Group au projet », sans montant, logo affiché (`partners.ionis`, fiche seed `IONIS Education Group`, `logo_consent=1`). La source texte du site actuel ne mentionne pas IONIS : décision de l’utilisateur. Confirmer le principe, la formulation et l’autorisation d’utiliser le logo.
2. **Phrase 3DEXPERIENCE** « soutient aussi d’autres associations » : validée par l’utilisateur, publiée telle quelle (aucun « étudiantes »). Le texte source dit aussi que le soutien porte sur « Joan et moi » : volontairement non repris (identité de l’auteur incertaine).
3. **« Un morceau de ciel bleu »** : fiche seed `published=0`, `logo_consent=0`, aucune description (aucune source textuelle ; logo présent dans `public/img`, nom visible sur le stand de `event-stand`). À publier uniquement après accord de l’association.
4. **Charge utile** : la source dit « carrying capacity of over 2 kg while keeping the total weight under 2 kg » (contradictoire en apparence). Publié : « augmenter la capacité d’emport tout en maintenant une masse à vide inférieure à 2 kg », sans chiffre de charge. Confirmer.
5. **HV3 / HV4 vs V3 / V4** : la source écrit « HV3 et HV4 » puis « V3 / V4 ». Publié partout « HV3 » et « HV4 » ; à confirmer.
6. **« hydroplanes »** (source) : le titre FR du projet dit « engins à effet de sol » (le mot « hydravion » n’apparaît plus nulle part, seed compris) ; en EN, choix de « ground-effect craft » à la place de « hydroplane(s) » (titre du projet, texte « why », article de seed). Ailleurs « aéronefs / aircraft à effet de sol ». Confirmer le terme.
7. **Photos des membres** : `photo_consent=1` pour les 8 (déjà publiques sur le site actuel). Vérifier l’accord de chacun en admin. `team.photo_note` affirme que chaque photo est publiée avec l’accord de la personne : à retirer si ce n’est pas vérifiable. Portraits : Chislain (`team-chislain`) en uniforme, Mathis avec arrière-plan à logos : à valider.
8. **Rôles des membres** : aucun rôle dans les sources ; tous affichent « Membre » (`team.role_default`).
9. **Frise** : aucune date (aucune source). Étiquettes « Étape 1 à 4 ». L’étape 4 « V6 : construction et essais » (`planned`) est déduite de l’objectif de la source (valider les calculs, élargir le domaine d’utilisation) ; aucune échéance annoncée.
10. **Page confidentialité** (à faire relire par l’association). Durées 12 mois / 24 mois / 24 h et « CV dans un dossier dont l’accès est interdit depuis internet » : cahier des charges et `docs/CONTRACTS.md` §9. Purge présentée comme automatique (contrôle quotidien déclenché par l’activité du site, sans promesse d’horaire) avec bouton de purge dans l’espace membres. Notification e-mail à l’association sans aucune donnée personnelle ; seul sous-traitant cité : l’hébergeur (nom issu de `ORG_HOST_NAME`, Google n’est plus cité). Localisation des données non précisée (ne pas écrire « France » sans confirmation).
11. **Journaux techniques : 90 jours au plus** : durée fixée par la consigne, non issue d’une source ; à confirmer (journaux du site et de l’hébergeur OVH, qui ne contiennent aucune donnée saisie par les visiteurs).
12. **Empreinte d’IP** : décrite pour les envois de formulaires et les tentatives de connexion de l’espace membres (« de quelques minutes à 24 heures au plus ») ; la date de dernière connexion des comptes membres est mentionnée. À confirmer avec le code (Core/Throttle, Auth).
13. **Bases légales** : candidature et contact = consentement (case cochée) ; droits = obligation légale (mention `rights.notice_html`, plus de case) ; limitation des envois, des tentatives de connexion et journaux techniques = intérêt légitime (formulation ajoutée par le module contenu, à valider).
14. **Cookie `abf_admin`** : déposé dès l’accès à la page de connexion de l’espace membres ; durée annoncée « durée de la session (12 heures au plus) » d’après `docs/CONTRACTS.md` §9 (session max 12 h).
15. **Mentions légales** : `ORG_LEGAL_FORM`, `ORG_RNA`, `ORG_ADDRESS`, `ORG_PUBLISHER` sont vides dans `.env.example` : ces lignes sont masquées ; la section hébergeur de `legal.php` ne s’affiche que si `host_name` (ou `host_address`) est fourni, sinon rien n’est montré (vérifié). Une association loi 1901 qui édite un site doit en principe indiquer l’adresse et le directeur de la publication : à renseigner avant la mise en production.
15 bis. **Avant production** : renseigner `ORG_HOST_NAME`, `ORG_HOST_ADDRESS`, `ORG_HOST_PHONE`, `ORG_PUBLISHER`, `ORG_ADDRESS`, `ORG_RNA` (la LCEN impose d’indiquer l’hébergeur et l’éditeur). La politique de confidentialité n’écrit plus aucun nom d’hébergeur en dur : elle utilise `ORG_HOST_NAME` (paramètre `:host`) et, si vide, « notre hébergeur » / « our hosting provider ». La section hébergeur de `legal.php` affiche nom, adresse et téléphone (`host_phone`), et reste masquée si tout est vide ; `PageController::org()` doit transmettre la clé `host_phone` (`HOST_PHONE` n’est pas encore dans sa liste de clés).
16. **URL des partenaires** (`https://www.ionis-group.com/`, `https://www.3ds.com/products/3dexperience`) : vérifiées en ligne (HTTP 200) mais non issues des sources ; à valider.
17. **Article de lancement V6** : rédigé à partir de la page Projet ; publié à la date de création du seed ; couverture `water-run` (photo d’une maquette rouge, non identifiée comme HV3, HV4 ou V6).

## Décisions appliquées

- Aucune marque de carte électronique citée : « architecture à double calculateur », enregistrements automatiques sur carte SD, réflexion sur des moteurs de manœuvre à l’arrêt (`project.electronics`).
- Section V6 limitée à la source : ailes basses (100 cm de chaque côté, corde de 50 cm), empennage en T en carbone, mousses étanches, étanchéité d’inspiration nautique, flotteurs carénés à cavités internes, train d’atterrissage amovible, incidence réglable en vol (piste d’innovation).
- Textes alternatifs : chaque image de `public/img/manifest.json` (34 slugs) décrite d’après ce qui est visible ; les portraits portent uniquement le nom ; aucune image ne nomme un prototype ni un lieu. Le portrait « Karim Benali » n’est pas utilisé.

## Conventions à connaître pour les autres contributeurs

- Paramètres des textes : `common.published_on` (`:date`), `news.page_of` (`:page`, `:pages`), `footer.copyright` (`:year`), `join|contact.consent_html` et `rights.notice_html` (`:privacy_url`, URL absolue de la page confidentialité), `forms.errors.summary_title` (`:count`). `rights.consent_html` est supprimé (FR/EN) : le formulaire des droits n’a plus de case. Nouvelles clés : `forms.required_legend`, `join.consent_short`, `contact.consent_short` (« Consentement » / « Consent »), `rights.types.restrict`, `privacy.retention.logs|auto`. Les `*_html` doivent être appelés avec le paramètre `:privacy_url`.
- `meta.title_suffix` contient le séparateur (« | Aéroboat France », espace initial compris).
- `home.stats` : 4 entrées valeur + libellé ; `partners.dx.*_label` sont des libellés (les valeurs 20 et 4 ne sont pas dans la langue).
- Vues juridiques : `$org` accepte les clés `name`, `ORG_NAME` ou `org_name` ; clés attendues : name, legal_form, rna, address, city, email, phone, publisher, host_name, host_address. Classes CSS utilisées : `legal-page container`, `page-hero`, `lead`, `prose`, `legal-list`, `legal-row`.
- Seed : `cover`, `photo`, `logo` = noms de base du manifeste d’images ; `author_id` nul ; l’idempotence se fait sur `name` (team, partners) et `slug_fr` (posts).
- Racine `admin_public` : 2 clés ajoutées (`restricted`, `link`), non listées dans LANG-KEYS.
