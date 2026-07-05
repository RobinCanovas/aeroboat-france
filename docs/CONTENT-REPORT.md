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
6. **« hydroplanes »** (source) traduit par « hydravions » dans le titre du projet (« nouvelle génération d’hydravions ») ; ailleurs « aéronefs à effet de sol ». Confirmer le terme.
7. **Photos des membres** : `photo_consent=1` pour les 8 (déjà publiques sur le site actuel). Vérifier l’accord de chacun en admin. `team.photo_note` affirme que chaque photo est publiée avec l’accord de la personne : à retirer si ce n’est pas vérifiable. Portraits : Chislain (`team-chislain`) en uniforme, Mathis avec arrière-plan à logos : à valider.
8. **Rôles des membres** : aucun rôle dans les sources ; tous affichent « Membre » (`team.role_default`).
9. **Frise** : aucune date (aucune source). Étiquettes « Étape 1 à 4 ». L’étape 4 « V6 : construction et essais » (`planned`) est déduite de l’objectif de la source (valider les calculs, élargir le domaine d’utilisation) ; aucune échéance annoncée.
10. **Page confidentialité** (à faire relire par l’association) : durées 12 mois / 24 mois / 24 h et « CV stocké hors web » viennent du cahier des charges ; cookie `abf_admin` (session) ; « notification par e-mail à l’adresse de contact » (MAIL_TO est une adresse Gmail : si elle est conservée, citer Google comme destinataire/sous-traitant) ; « journaux techniques de l’hébergeur » (à vérifier chez OVH) ; localisation des données non précisée (ne pas écrire « France » sans confirmation).
11. **Mentions légales** : `ORG_LEGAL_FORM`, `ORG_RNA`, `ORG_ADDRESS`, `ORG_PUBLISHER` sont vides dans `.env.example` : ces lignes sont masquées. Une association loi 1901 qui édite un site doit en principe indiquer l’adresse et le directeur de la publication : à renseigner avant la mise en production.
12. **URL des partenaires** (`https://www.ionis-group.com/`, `https://www.3ds.com/products/3dexperience`) : vérifiées en ligne (HTTP 200) mais non issues des sources ; à valider.
13. **Article de lancement V6** : rédigé à partir de la page Projet ; publié à la date de création du seed ; couverture `water-run` (photo d’une maquette rouge, non identifiée comme HV3, HV4 ou V6).

## Décisions appliquées

- Aucune marque de carte électronique citée : « architecture à double calculateur », enregistrements automatiques sur carte SD, réflexion sur des moteurs de manœuvre à l’arrêt (`project.electronics`).
- Section V6 limitée à la source : ailes basses (100 cm de chaque côté, corde de 50 cm), empennage en T en carbone, mousses étanches, étanchéité d’inspiration nautique, flotteurs carénés à cavités internes, train d’atterrissage amovible, incidence réglable en vol (piste d’innovation).
- Textes alternatifs : chaque image de `public/img/manifest.json` (34 slugs) décrite d’après ce qui est visible ; les portraits portent uniquement le nom ; aucune image ne nomme un prototype ni un lieu. Le portrait « Karim Benali » n’est pas utilisé.

## Conventions à connaître pour les autres contributeurs

- Paramètres des textes : `common.published_on` (`:date`), `news.page_of` (`:page`, `:pages`), `footer.copyright` (`:year`), `join|contact|rights.consent_html` (`:privacy_url`, URL absolue de la page confidentialité). Les `*_html` doivent donc être appelés avec ce paramètre.
- `meta.title_suffix` contient le séparateur (« | Aéroboat France », espace initial compris).
- `home.stats` : 4 entrées valeur + libellé ; `partners.dx.*_label` sont des libellés (les valeurs 20 et 4 ne sont pas dans la langue).
- Vues juridiques : `$org` accepte les clés `name`, `ORG_NAME` ou `org_name` ; clés attendues : name, legal_form, rna, address, city, email, phone, publisher, host_name, host_address. Classes CSS utilisées : `legal-page container`, `page-hero`, `lead`, `prose`, `legal-list`, `legal-row`.
- Seed : `cover`, `photo`, `logo` = noms de base du manifeste d’images ; `author_id` nul ; l’idempotence se fait sur `name` (team, partners) et `slug_fr` (posts).
- Racine `admin_public` : 2 clés ajoutées (`restricted`, `link`), non listées dans LANG-KEYS.
