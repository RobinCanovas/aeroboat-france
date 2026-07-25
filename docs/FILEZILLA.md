# Mettre le site de test en ligne avec FileZilla

Le site actuel (WordPress) reste intact. La v2 va dans un **sous-dossier séparé** : `/www/test/`, visible sur `https://aeroboat-france.com/test/`.

## 0. Ce que vous aurez à envoyer
| Quoi | D'où (sur votre PC) | Où (sur OVH) |
|---|---|---|
| Le site complet | **le contenu** du dossier `aeroboat-france-v2\dist\` (après `npm run release`) | `/www/test/` |
| Le fichier de configuration | `aeroboat-france-v2\.env.test` → à envoyer **sous le nom `.env`** | `/www/test/.env` |

Ne rien envoyer d'autre. Ne rien toucher dans `/www/` en dehors du dossier `test`.

## 1. Fabriquer le dossier à envoyer
Dans un terminal, dans `aeroboat-france-v2` : `npm run release`. Le dossier `dist\` est créé (le script vérifie qu'il ne contient ni secret ni fichier inutile).

## 2. Connexion FileZilla
Fichier → Gestionnaire de sites → Nouveau site :
- Protocole : **FTP**, Hôte : `<hôte FTP de l'hébergeur>`, Port : 21
- Chiffrement : **Exiger un FTP explicite sur TLS** (FTPS explicite)
- Authentification : Normale, Identifiant : `<identifiant FTP>`, Mot de passe : celui de l'hébergement
- Menu **Serveur → Forcer l'affichage des fichiers cachés** (indispensable : les fichiers `.htaccess` et `.env` commencent par un point).

## 3. Envoi
1. Côté serveur (panneau de droite), ouvrir `/www/`. Vous y voyez le WordPress actuel : **ne rien y modifier**.
2. Créer le dossier `test` (clic droit → Créer un répertoire) si absent, puis l'ouvrir.
3. Côté PC (panneau de gauche), ouvrir `aeroboat-france-v2\dist\`, tout sélectionner (Ctrl+A) et glisser **le contenu** dans `/www/test/`. Vous devez voir dans `/www/test/` : `.htaccess`, `app`, `database`, `public`, `storage`.
4. Côté PC, aller dans `aeroboat-france-v2\`, clic droit sur `.env.test` → **Envoyer**, puis côté serveur clic droit sur ce fichier → **Renommer** en `.env` (dans `/www/test/`).
5. Si FileZilla demande d'écraser : « Écraser si source plus récente » pour les mises à jour ; pour `.env`, n'écrasez que si vous avez changé la configuration.
6. Pour une mise à jour ultérieure : refaire `npm run release` et renvoyer le contenu de `dist\`. **Ne supprimez jamais** `storage/` ni `public/uploads/` (CV et images envoyés par les visiteurs) et ne remplacez pas `.env`.

## 4. Première ouverture
1. Ouvrir `https://aeroboat-france.com/test/` : la page d'accueil apparaît avec le bandeau « version de test ».
2. Ouvrir `https://aeroboat-france.com/test/admin/setup`. Le **jeton d'installation** est la valeur `SETUP_TOKEN` de votre fichier `.env.test` (ouvrez-le avec le Bloc-notes, ne le partagez pas). Choisissez votre identifiant et un mot de passe de 12 caractères minimum : l'installation crée les tables `abf_…` dans la base (les tables WordPress `mod759_…` ne sont jamais touchées), charge le contenu de départ et crée le premier compte administrateur.
3. La page d'installation disparaît (erreur 404) dès qu'un compte existe. Ensuite, retirez la ligne `SETUP_TOKEN` du `.env` du serveur si vous voulez.
4. Connexion ensuite sur `https://aeroboat-france.com/test/admin/login`.

## 5. Contrôles après envoi
Ces adresses doivent répondre **404 ou 403** (jamais afficher de contenu) : `/test/.env`, `/test/app/`, `/test/storage/`, `/test/database/`.
Si une de ces adresses affiche quelque chose : arrêtez, ne communiquez pas l'adresse, et prévenez-moi.

## 6. Si ça ne marche pas
- Page blanche ou erreur 500 : vérifier que `.htaccess` est bien présent dans `/www/test/` (fichiers cachés affichés) et que `.env` existe.
- Erreur de base de données : le fichier `.env` doit être celui généré par `.env.test` (pas le modèle `.env.example`).
- Le site WordPress ne doit pas avoir changé : testez `https://www.aeroboat-france.com/`.

## 7. Plus tard : `test.aeroboat-france.com`
OVH Manager → Hébergements → Multisite → Ajouter un domaine (sous-domaine `test`), dossier racine `test` ; puis dans `.env`, changer `APP_URL=https://test.aeroboat-france.com` et renvoyer ce fichier. Le contenu de `dist\` ne change pas.

## 8. À la fin
Changez le mot de passe FTP de l'hébergement (il a été échangé dans un chat).
