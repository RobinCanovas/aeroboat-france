# Aéroboat France v2

Site de **test** de l'association Aéroboat France (la production WordPress reste intouchée) : PHP 8.2 natif, PDO (SQLite en local, MySQL chez OVH), Vite, mini-CMS maison, FR/EN, aucun cookie visiteur.

Conventions et contrats : [`CONTRIBUTING.md`](CONTRIBUTING.md), [`docs/CONTRACTS.md`](docs/CONTRACTS.md).

## Prérequis

| Outil | Version | Installation (Windows) |
|---|---|---|
| Node.js | 20 ou plus | <https://nodejs.org> |
| PHP | **8.2** (cible OVH ; une version plus récente fonctionne en local, un avertissement s'affiche) | `winget install PHP.PHP.8.2` |
| Git | récent | `winget install Git.Git` |

Extensions PHP utilisées : `pdo_sqlite` (local), `pdo_mysql` (OVH), `mbstring`, `gd`, `fileinfo`. Les scripts cherchent PHP dans le `PATH`, puis dans `C:\wamp64\bin\php\php8.2.*`, puis dans les paquets WinGet. Variable `PHP_BIN` pour forcer un exécutable. S'il est introuvable, le script s'arrête (code 1) en affichant la commande d'installation.

## Installation locale

```bash
npm install
npm run dev          # http://127.0.0.1:8000 (PHP) + Vite (HMR)
```

Au premier lancement, `npm run dev` crée un `.env` de démonstration **s'il n'existe pas** (SQLite `database/dev.sqlite`, `APP_KEY` et `SETUP_TOKEN` aléatoires, aucun secret réel), puis migre et alimente la base. `Ctrl+C` arrête proprement PHP et Vite.

## Commandes

| Commande | Effet |
|---|---|
| `npm run dev` | Vite + `php -S` (routeur `scripts/dev-router.php`) |
| `npm test` | tests PHP (parité FR/EN, clés utilisées, mot interdit, compatibilité PHP 8.2) |
| `npm run lint:php` | `php -l` sur `app/ database/ public/ tests/ scripts/` (code 1 si erreur) |
| `npm run build` | assets Vite vers `public/assets/` |
| `npm run migrate:demo` | migration + seed sur SQLite temporaire |
| `npm run ci` | lint + tests + build + migration de démonstration (ce que fait GitHub Actions) |
| `npm run release` | construit `dist/` prêt pour FTP |
| `npm run deploy:test` | envoie `dist/` vers `/www/test/` (ajouter `-- --dry-run` pour simuler) |
| `npm run push-env` | envoie `.env.test` vers `/www/test/.env` |
| `node --test "scripts/tests/*.test.mjs"` | tests des scripts de déploiement (serveur FTP factice local, aucun réseau) |

## Configuration (`.env`)

Modèle : [`.env.example`](.env.example). Les fichiers `.env*` sont ignorés par git (sauf `.env.example`). Clés principales : `APP_ENV`, `APP_DEBUG`, `APP_URL` (son chemin sert de préfixe : `https://aeroboat-france.com/test`), `APP_KEY` (64 hex), `SETUP_TOKEN`, `SITE_NOINDEX`, `DB_*` (jamais le préfixe `mod759_`, réservé à WordPress), `MAIL_*`, `ORG_*` (mentions légales). Une valeur contenant des espaces, `#`, `"` ou `\` est écrite entre guillemets doubles (`\"` et `\\` échappés).

## Premier administrateur

1. Ouvrir `<APP_URL>/admin/setup?token=<SETUP_TOKEN>` (le jeton est dans le `.env` correspondant).
2. Créer le compte `admin` (bureau). La page se désactive dès qu'un compte existe.
3. Se connecter sur `<APP_URL>/admin`. Les rédacteurs (`editor`) se créent ensuite depuis l'interface.

## Fichiers que vous créez vous-même

Aucun identifiant n'est dans le dépôt. Créez à la racine (ils sont ignorés par git) :

**`.env.deploy`** : accès FTP OVH

```ini
FTP_HOST=<hôte FTP de l'hébergeur>
FTP_USER=<identifiant FTP>
FTP_PASSWORD=<votre mot de passe FTP>
FTP_SECURE=true
FTP_REMOTE_DIR=/www/test
```

- `FTP_SECURE=true` : FTPS explicite (par défaut). Le FTP en clair est refusé sauf `FTP_SECURE=false` **et** option `--allow-plain-ftp`.
- `FTP_INSECURE_TLS=true` (optionnel, **jamais par défaut**) : ne vérifie pas le certificat du serveur. À réserver à un dépannage ponctuel (voir Dépannage).
- `FTP_PORT` (optionnel, 21 par défaut).

**`.env.test`** : configuration du site de test, générée sans rien afficher à partir des accès MySQL de WordPress :

```bash
node scripts/extract-db-env.mjs                      # lit ../SiTE/_/www/wp-config.php
node scripts/extract-db-env.mjs --wp-config <chemin> # autre emplacement
node scripts/extract-db-env.mjs --force              # écraser un .env.test existant
```

Le script écrit `.env.test` (`APP_URL=https://aeroboat-france.com/test`, `APP_KEY` et `SETUP_TOKEN` aléatoires, `DB_DRIVER=mysql`, `DB_PREFIX=abf_`, `APP_ENV=test`, `APP_DEBUG=0`, `SITE_NOINDEX=1`, `ORG_*`), n'affiche **que les noms** des clés et refuse d'écraser sans `--force`.

## Déploiement manuel vers `/www/test/`

```bash
npm run release                          # dist/ (vite build, php -l, contrôle des secrets, RELEASE.json)
npm run deploy:test -- --dry-run         # simulation : liste ce qui serait envoyé/supprimé
npm run deploy:test                       # envoi réel
npm run push-env                          # une première fois, puis à chaque changement de .env.test
```

Fonctionnement et garde-fous :

- **État par hash** (`.deploy-state-test.json`, ignoré par git) : seuls les fichiers modifiés sont renvoyés ; l'état n'avance qu'après chaque envoi réussi, une interruption se reprend en relançant la commande. Nouvelles tentatives avec temporisation croissante.
- **Suppression sûre** : seuls les fichiers que ce script a lui-même envoyés peuvent être supprimés ; jamais hors de `FTP_REMOTE_DIR`, jamais `storage/`, `public/uploads/`, `.env`.
- `storage/.htaccess` et `public/uploads/.htaccess` sont en **création seule** (jamais écrasés ni supprimés). Le contenu de `storage/` et `public/uploads/` (CV, photos téléversées) n'est jamais envoyé.
- **Garde-fous production** : `FTP_REMOTE_DIR` vide, `/`, `/www`, `/www/` ou hors de `/www/…` : refus. Tout autre nom que `/www/test` exige `--i-know-what-i-am-doing`.
- `push-env` refuse si `.env.test` contient `DB_PREFIX=mod759_` ou n'est pas ignoré par git (`git check-ignore`).
- Le premier envoi doit passer par `npm run deploy:test` (il crée `storage/` et `public/uploads/`, que l'action GitHub n'envoie pas).
- Vérifier une fois que `https://aeroboat-france.com/test/.env` renvoie 403/404 (le `.htaccess` racine doit bloquer les fichiers cachés).

## Déploiement via GitHub

```bash
git remote add origin https://github.com/<compte>/aeroboat-france-v2.git
git push -u origin main develop
```

Dans GitHub : *Settings → Secrets and variables → Actions → New repository secret* :

| Secret | Valeur |
|---|---|
| `FTP_SERVER` | `<hôte FTP de l'hébergeur>` |
| `FTP_USERNAME` | l'identifiant FTP |
| `FTP_PASSWORD` | le mot de passe FTP |

- `.github/workflows/ci.yml` : à chaque push/PR, PHP 8.2 + Node 20, `npm ci`, `npm run ci` et tests des scripts.
- `.github/workflows/deploy-test.yml` : sur push de `develop` ou lancement manuel (*Actions → Déploiement test → Run workflow*) : build de la release puis envoi FTPS de `dist/` vers `/www/test/`, en excluant `storage/**`, `public/uploads/**` et `.env`, sans jamais vider le dossier distant.
- Le `.env` du serveur reste géré par `npm run push-env` (jamais par GitHub).

## Passer à `test.aeroboat-france.com`

1. OVH Manager → *Web Cloud → Hébergements → Multisite* : ajouter le sous-domaine `test.aeroboat-france.com` avec **dossier racine `test`** (certificat SSL activé).
2. Dans `.env.test` : `APP_URL=https://test.aeroboat-france.com` (le préfixe `/test` disparaît).
3. `npm run push-env`, puis vérifier le site et `/admin`.

## Ajouter une langue

1. Créer `app/lang/<code>.php` à partir de `fr.php` (mêmes clés et mêmes paramètres `:nom`, le français reste la référence).
2. Déclarer la langue dans `app/routes.php` (chemins localisés) et dans la liste des langues du Core.
3. Ajouter les champs `_<code>` aux contenus éditables en base (migration `database/migrations/NNN_*.sql`) et à `seed-content.php`.
4. `npm test` : la parité des clés doit rester verte.

## Ajouter un module (espace membres)

Créer `app/Modules/<Nom>/` avec `module.php` (retourne `key`, `label`, `icon`, `route` `/admin/modules/<key>`, `roles`, `controller`, `method`) et un dossier `views/`. Le menu de l'admin le découvre automatiquement (voir `docs/CONTRACTS.md` §7).

## Purge RGPD

Les candidatures (`applications`) et messages (`messages`) sont stockés en base ; les CV PDF dans `storage/cv/` (hors web). Pour une demande d'effacement : supprimer la ligne depuis l'admin, supprimer le PDF correspondant de `storage/cv/` (FTP), puis vérifier le journal. Les IP ne sont jamais conservées en clair (hachées avec `APP_KEY`). Prévoir une purge périodique des candidatures traitées (durée de conservation à fixer par le bureau).

## Dépannage

- **Erreur TLS / certificat FTPS** (`self-signed`, `hostname mismatch`) : OVH présente parfois un certificat au nom du cluster. Vérifier `FTP_HOST`. En dernier recours seulement et pour un envoi ponctuel : `FTP_INSECURE_TLS=true` dans `.env.deploy` (le certificat n'est alors plus vérifié ; retirer la ligne ensuite).
- **`530 Login incorrect`** : identifiant ou mot de passe FTP erroné (le mot de passe n'est jamais affiché). **Timeout / liste de dossier** : le mode passif peut être bloqué par un pare-feu ou un VPN.
- **404 sur toutes les pages, ou règles WordPress qui reviennent** : un `.htaccess` WordPress à la racine de `/www/` ne doit pas s'appliquer à `/www/test/`. Le `.htaccess` du site de test (`.htaccess` racine du dépôt) doit gérer lui-même la réécriture ; s'il manque, `npm run release` échoue.
- **Les mails n'arrivent pas** : le site utilise `mail()` en « best effort » ; la base reste la source de vérité (consulter les messages dans l'admin). Vérifier `MAIL_TO`/`MAIL_FROM` (adresse du domaine) et les spams.
- **`npm run dev` : PHP introuvable** : `winget install PHP.PHP.8.2`, rouvrir le terminal.
- **Vite « manifest.json absent » à la release** : `vite.config.js` doit produire `public/assets/.vite/manifest.json` (`build.manifest: true`).

## Fin de mission : changer le mot de passe FTP

Le mot de passe FTP a transité par des outils de développement : **changez-le dans OVH Manager** (*Hébergements → FTP-SSH → Modifier le mot de passe*) à la fin de la mission, mettez à jour `.env.deploy` et le secret GitHub `FTP_PASSWORD`. Même précaution pour le mot de passe MySQL si `wp-config.php` a été manipulé.
