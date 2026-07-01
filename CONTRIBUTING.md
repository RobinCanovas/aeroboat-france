# Aéroboat France v2 — conventions du projet

Site de **test** (la prod WordPress reste intouchée). Association étudiante toulousaine, maquettes à effet de sol. PHP 8.2 natif, PDO, Vite, mini-CMS maison.

## Règles non négociables
1. **Prod intacte** : ne jamais écrire à la racine `/www/` d'OVH. Seul `/www/test/` est déployé. Ne jamais toucher aux tables `mod759_` ; préfixe `abf_`.
2. **Pas de framework PHP, pas de Composer.** PHP 8.2 : aucune syntaxe 8.3+ (pas de `const string X`, `#[\Override]`, `json_validate`, `array_find/any/all`, hooks de propriété, visibilité asymétrique). Le PHP local est plus récent : `php tests/run.php` contient un contrôle de compatibilité 8.2.
3. **SQL portable SQLite ↔ MySQL** : migrations avec les jetons `{{PREFIX}}`, `{{PK}}`, `{{OPTS}}` (voir docs/CONTRACTS.md).
4. **FR/EN** : français = langue de référence. Tout texte d'interface dans `app/lang/fr.php` + `en.php`, mêmes clés (test de parité). URLs localisées (`/fr/projet`, `/en/project`) via `app/routes.php`.
5. **RGPD** : zéro traceur, zéro CDN externe, polices auto-hébergées, **aucun cookie visiteur** (CSRF public stateless HMAC). Seul l'admin a une session.
6. **Accessibilité WCAG AA** : clavier, contrastes, `prefers-reduced-motion`, mobile-first (360 / 768 / 1280 px).
7. **Secrets** : `.env*` ignorés par git (sauf `.env.example`). Ne jamais lire, afficher ni copier `wp-config.php` ; le seul usage autorisé est `node scripts/extract-db-env.mjs` qui écrit `.env.test` sans rien afficher. Aucun secret dans les commits, logs, rapports.
8. **Version de test** : `noindex`, `Disallow: /`, bandeau « version de test » (`SITE_NOINDEX=1`).
9. **Pas de WordPress, pas d'Arduino** : le mot « Arduino » n'apparaît nulle part (écrire « architecture à double calculateur »).
10. **CSP stricte** : aucun `style=""`, aucun `onclick=""`, aucun script inline sans `nonce`.
11. N'inventer aucun fait : le contenu vient de `wp-json` de la prod et du backlog. Doute = marquer « à confirmer » dans `docs/CONTENT-REPORT.md`.
12. Le portrait « Karim Benali » n'est pas un membre : ne jamais l'utiliser. La fiche partenaire « Un morceau de ciel bleu » reste `published=0`.

## Commandes
- `npm run dev` : Vite + serveur PHP intégré (SQLite local)
- `npm run test` : `php tests/run.php` (parité lang, clés utilisées, Arduino, compat PHP 8.2)
- `npm run lint:php` : `php -l` sur tous les fichiers PHP
- `npm run build` : assets Vite → `public/assets/`
- `npm run migrate:demo` : migration + seed sur SQLite temporaire
- `npm run release` : construit `dist/` (prêt pour FTP)
- `npm run deploy:test` / `npm run push-env` : déploiement vers `/www/test/` (nécessite `.env.deploy`, créé par l'utilisateur)

## Organisation
Voir `docs/CONTRACTS.md` (contrats figés : routes, SQL, API Core, lang, tokens CSS, propriété des fichiers). Chaque module reste dans son périmètre de fichiers ; un besoin hors périmètre se discute avec le mainteneur.

Git : `main` (stable) ← `develop` (intégration) ← branches `feature/<nom>`. Commits en français ou anglais, courts, à l'impératif.

## Style de code
PHP : `declare(strict_types=1);`, namespace `App\…`, autoload maison `app/bootstrap.php`, requêtes préparées PDO uniquement, échappement `e()` à la sortie. JS : ES modules, aucune dépendance runtime. CSS : jetons de `frontend/css/tokens.css`, mobile-first, pas de framework.
