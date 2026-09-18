# Aéroboat France v2

Nouveau site de l'association **Aéroboat France**, une association étudiante toulousaine qui conçoit, construit et teste des maquettes d'aéronefs à effet de sol (les « aéroboats »). Le site présente le projet, l'équipe, les partenaires et les actualités, permet de contacter l'association ou de candidater, et inclut un petit calculateur d'effet de sol.

Il remplace à terme le site WordPress actuel. Pendant la phase de test, il est hébergé dans un sous-dossier séparé et n'est pas indexé par les moteurs de recherche.

## Ce que fait le site

- **Pages publiques** : accueil, projet, équipe, partenaires, actualités, rejoindre l'association, contact.
- **Deux langues** : français (langue de référence) et anglais, avec des adresses localisées (`/fr/projet`, `/en/project`).
- **Respect de la vie privée** : aucun cookie côté visiteur, aucun traceur, aucun service externe, polices hébergées sur le site. Les formulaires sont protégés sans cookie (jeton signé, champ piège, délai minimum).
- **Accessibilité** : navigation au clavier, contrastes AA, respect de `prefers-reduced-motion`, mise en page pensée d'abord pour mobile.
- **Espace membres** (`/admin`) : mini-CMS pour gérer l'équipe, les partenaires et les actualités, lire les messages et les candidatures, et purger les données personnelles. Les accès sont répartis entre administrateurs (bureau) et rédacteurs.
- **Module Calculateur** : estimation des effets de sol, pensé comme le premier d'une série de modules ajoutables à l'espace membres.
- **Pages légales** : mentions légales, confidentialité, cookies, exercice des droits.

## Technique

| | |
|---|---|
| Langage | PHP 8.2 natif, sans framework ni Composer |
| Base de données | PDO : SQLite en local, MySQL en hébergement. Migrations en SQL portable |
| Front | CSS et JavaScript maison, assets construits avec Vite. Le site reste utilisable sans JavaScript |
| Contenu | éditable en base, en français et en anglais |
| Déploiement | scripts Node (FTPS) ou GitHub Actions |

## Prérequis

- Node.js 20 ou plus
- PHP 8.2 (une version plus récente fonctionne en local, avec un avertissement), avec les extensions `pdo_sqlite`, `pdo_mysql`, `mbstring`, `gd` et `fileinfo`
- Git

Sous Windows : `winget install PHP.PHP.8.2`. Les scripts cherchent PHP dans le `PATH`, puis dans les installations WAMP et WinGet. La variable `PHP_BIN` permet de forcer un exécutable.

## Démarrer en local

```bash
npm install
npm run dev
```

Le site est alors disponible sur <http://127.0.0.1:8000>, avec rechargement à chaud des assets.

Au premier lancement, un fichier `.env` de démonstration est créé s'il n'existe pas (base SQLite, clés aléatoires, aucun secret réel), puis la base est migrée et remplie avec le contenu de départ. `Ctrl+C` arrête proprement PHP et Vite.

Pour créer le premier compte administrateur, ouvrir `/admin/setup?token=<SETUP_TOKEN>` (le jeton est dans le `.env`). La page se désactive dès qu'un compte existe. Les rédacteurs se créent ensuite depuis l'interface.

## Commandes

| Commande | Effet |
|---|---|
| `npm run dev` | serveur PHP et Vite |
| `npm test` | tests PHP (parité FR/EN, clés de langue utilisées, compatibilité PHP 8.2, base, authentification, admin) |
| `npm run test:scripts` | tests des scripts de déploiement, sur un serveur FTP factice local |
| `npm run lint:php` | vérification de syntaxe PHP |
| `npm run build` | construit les assets dans `public/assets/` |
| `npm run migrate:demo` | migration et contenu de départ sur une base temporaire |
| `npm run ci` | enchaîne tout ce qui précède (c'est ce que fait GitHub Actions) |
| `npm run release` | prépare le dossier `dist/` à envoyer sur l'hébergement |
| `npm run deploy:test` | envoie `dist/` sur le site de test (`-- --dry-run` pour simuler) |
| `npm run push-env` | envoie la configuration du site de test |

## Organisation du dépôt

```
app/            code PHP : Core (noyau), Controllers, Admin, Modules, vues, textes FR/EN
database/       migrations SQL, contenu de départ
frontend/       CSS, JavaScript, polices et images sources
public/         point d'entrée web et dossier des téléversements
scripts/        développement, build, déploiement
tests/          tests PHP
docs/           contrats techniques, rapport de contenu, guide de mise en ligne
```

Les choix techniques détaillés (routes, schéma SQL, API du noyau, clés de langue, jetons CSS) sont dans [`docs/CONTRACTS.md`](docs/CONTRACTS.md).

## Configuration

Le modèle est [`.env.example`](.env.example). Les fichiers `.env*` ne sont jamais versionnés. Les réglages principaux :

- `APP_ENV`, `APP_DEBUG`, `APP_URL` (son chemin sert de préfixe si le site est dans un sous-dossier), `APP_KEY`, `SETUP_TOKEN`, `SITE_NOINDEX`
- `DB_*` pour la base de données
- `MAIL_*` pour les notifications
- `ORG_*` pour l'identité de l'association affichée dans les mentions légales (les champs vides sont masqués)

Une valeur contenant des espaces, `#`, `"` ou `\` s'écrit entre guillemets doubles.

## Mise en ligne

Le site de test est envoyé en FTPS dans un sous-dossier dédié. Les identifiants d'accès ne sont jamais dans le dépôt : ils se placent dans un fichier `.env.deploy` à la racine, ignoré par git.

```ini
FTP_HOST=<hôte FTP de l'hébergeur>
FTP_USER=<identifiant>
FTP_PASSWORD=<mot de passe>
FTP_SECURE=true
FTP_REMOTE_DIR=/www/test
```

```bash
npm run release                   # construit dist/
npm run deploy:test -- --dry-run  # simulation
npm run deploy:test               # envoi réel
npm run push-env                  # envoie le .env du site de test (au premier envoi, puis à chaque changement)
```

Quelques garde-fous :

- seuls les fichiers modifiés sont renvoyés, et une interruption se reprend en relançant la commande ;
- le script ne supprime que ce qu'il a lui-même envoyé, et jamais `storage/`, `public/uploads/` ni `.env` ;
- la racine du site de production est protégée : le script refuse d'y écrire ;
- la release échoue si elle contient un secret ou un fichier de configuration sensible.

Le workflow GitHub Actions `deploy-test.yml` fait la même chose à chaque push sur `develop`, avec des secrets du dépôt (`FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD`). Le guide pas à pas avec FileZilla est dans [`docs/FILEZILLA.md`](docs/FILEZILLA.md).

## Étendre le site

**Ajouter une langue** : créer `app/lang/<code>.php` à partir de `fr.php` (mêmes clés), déclarer la langue dans `app/routes.php`, ajouter les champs `_<code>` aux contenus en base par une migration, puis lancer `npm test` pour vérifier la parité.

**Ajouter un module** : créer `app/Modules/<Nom>/` avec un fichier `module.php` (clé, libellé, route, rôles autorisés, contrôleur) et un dossier `views/`. Le menu de l'espace membres le détecte automatiquement.

## Données personnelles

Les candidatures et les messages sont stockés en base, les CV dans `storage/cv/` (hors du web). Pour une demande d'effacement, supprimer l'entrée depuis l'admin, supprimer le CV correspondant, puis vérifier le journal. Les adresses IP ne sont jamais conservées en clair. La durée de conservation des candidatures traitées reste à fixer par le bureau.

## Dépannage

- **Certificat FTPS refusé** : vérifier `FTP_HOST`. En dernier recours et pour un envoi ponctuel, `FTP_INSECURE_TLS=true` désactive la vérification du certificat (à retirer ensuite).
- **`530 Login incorrect`** : identifiant ou mot de passe FTP erroné.
- **Délai dépassé lors de l'envoi** : le mode passif peut être bloqué par un pare-feu ou un VPN.
- **404 sur toutes les pages** : un `.htaccess` d'un site parent peut interférer. Le `.htaccess` du dépôt gère lui-même la réécriture d'adresses.
- **Les mails n'arrivent pas** : l'envoi est « au mieux ». La base reste la référence, les messages se lisent dans l'admin.
- **PHP introuvable** : installer PHP 8.2 puis rouvrir le terminal.
