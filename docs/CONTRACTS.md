# Contrats figés (Phase 0)

Toute modification d'un contrat passe par le mainteneur du dépôt.

## 1. Propriété des fichiers

| Module | Périmètre exclusif |
|---|---|
| `core-back` | `app/bootstrap.php`, `app/Core/**`, `app/Controllers/**`, `database/**` (hors `001_init.sql` figé et `seed-content.php`), `public/index.php`, `public/.htaccess`, `.htaccess`, `storage/.htaccess`, `tests/core_*_test.php` |
| B `contenu-i18n` | `app/lang/**`, `app/views/pages/legal*.php`, `app/views/pages/privacy.php`, `app/views/pages/cookies.php`, `database/seed-content.php`, `docs/CONTENT-REPORT.md` |
| `front-design` | `frontend/**` (sauf `admin*`), `vite.config.js`, `app/views/**` (sauf `admin/`, pages juridiques), `public/img/**`, `public/favicon*`, `public/manifest.webmanifest` |
| `cms-admin` | `app/Admin/**`, `app/Modules/**`, `app/views/admin/**`, `frontend/admin.js`, `frontend/css/admin.css`, `tests/admin_*_test.php` |
| `devops` | `scripts/**` (hors `build-images.py`), `.github/**`, `README.md`, `tests/run.php` ne change pas |
| Mainteneur | `CONTRIBUTING.md`, `docs/**` (hors rapports), `package.json`, `.env.example`, `database/migrations/001_init.sql`, `app/routes.php`, `frontend/css/tokens.css`, `tests/` (runner et tests transverses) |

## 2. Routes (`app/routes.php`, figé)
Chaque entrée : clé → chemins FR/EN (sans slash initial), contrôleur, méthodes. URL publique = `APP_URL` + `/{lang}/` + chemin. `{slug}` = paramètre.
`/` redirige vers `/fr/` ou `/en/` selon `Accept-Language` (défaut fr). `/sitemap.xml`, `/robots.txt` sont non localisés. Tout chemin commençant par `/admin` part vers `App\Admin\Router::dispatch()` (non localisé, interface en français). 404 → `ErrorController::notFound`.

## 3. Base de données
Migrations dans `database/migrations/NNN_nom.sql`, une instruction par `;` en fin de ligne (pas de `;` dans les chaînes). Jetons remplacés par `Migrator` :
- `{{PREFIX}}` → `DB_PREFIX` (jamais `mod759_`) ; `{{PK}}` → clé auto-incrémentée (SQLite : `INTEGER PRIMARY KEY AUTOINCREMENT`, MySQL : `INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY`) ; `{{OPTS}}` → `ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci` (MySQL) ou vide (SQLite).
- Types autorisés : `INTEGER`, `VARCHAR(n)`, `TEXT`, `DATETIME`. Pas de `DEFAULT` sur `TEXT`, pas de clés étrangères, pas de `IF NOT EXISTS` sur les index. Dates en UTC `Y-m-d H:i:s` produites par PHP (`gmdate`), jamais `NOW()`.
- Visibilité d'un article : `status='published'` OU (`status='scheduled'` ET `publish_at <= maintenant UTC`).
- `seed.php` : idempotent, lit `database/seed-content.php` (tableaux `team`, `partners`, `posts`). « Un morceau de ciel bleu » : `published=0`.

## 4. API du Core (`core-back` implémente, `front-design` et `cms-admin` consomment)
Namespace `App\Core`. Autoload PSR-4 maison (`App\X\Y` → `app/X/Y.php`).
- `Config::env(string $k, ?string $default=null): ?string` ; `Config::get('app.url')` (base sans slash final), `Config::basePath()` (chemin de `APP_URL`, ex. `/test`), `Config::isTest()`, `Config::root()`.
- `Db::pdo()`, `Db::table(string $name): string` (préfixé), `Db::all($sql,$params=[]): array`, `Db::one(...)`: ?array, `Db::run(...)`, `Db::insert(string $table,array $data): int`, `Db::update(string $table,int $id,array $data): void`, `Db::delete(string $table,int $id): void`. Dans le SQL, écrire `{t:users}` pour le nom préfixé.
- `Request` : `method()`, `path()` (sans base), `input(string $k,$default=null)`, `file(string $k): ?array`, `ip()`, `lang()` ; `Response` : `redirect($url,$code=302)`, `html($s,$code=200)`, `json($data,$code=200)`, `download($path,$name,$mime)`, `notFound()`.
- `Lang` : `Lang::current()`, `Lang::t($key,$params=[])`, `Lang::has($key)`. Paramètres `:nom`. Une clé absente renvoie la clé (et journalise en debug).
- `Router::url(string $key,array $params=[],?string $lang=null): string` (absolu), `Router::alternates(): array` (`['fr'=>url,'en'=>url]` pour la route courante).
- `View::render(string $tpl,array $data=[]): string` (gabarit `app/views/{tpl}.php`, enveloppé dans `layout.php` si `$data['_layout'] !== false`) ; `View::partial(string $tpl,array $data=[]): string`.
- `Csrf::formToken(string $form): string` / `Csrf::verifyForm(string $form,string $token,int $minDelay=3,int $maxAge=7200): bool` (HMAC sans cookie : `ts.hmac(APP_KEY, form|ts)`) ; `Csrf::sessionToken(): string` / `Csrf::verifySession(string $t): bool` (admin).
- `Throttle::hit(string $bucket,int $limit,int $windowSec): bool` (false = limite atteinte ; IP hachée avec `APP_KEY`, jamais en clair) ; `Throttle::purge()`.
- `Auth` (session admin) : `Auth::user(): ?array`, `Auth::attempt($username,$password): bool` (régénère l'ID de session), `Auth::logout()`, `Auth::requireRole(string ...$roles): void`, `Auth::hasRole(string $r): bool`. Rôles : `admin` (bureau, tout), `editor` (rédacteur : articles, équipe, partenaires, modules).
- `Session::start()` : uniquement côté admin. Cookie `abf_admin`, `HttpOnly`, `SameSite=Strict`, `Secure` si https, chemin = `basePath/admin`, stockage `storage/sessions/`.
- `Upload::image(array $file,string $subdir,int $maxWidth=1600): string` (GD, ré-encode en WebP, retourne chemin relatif à `public/uploads/`) ; `Upload::pdf(array $file): string` (vérifie `%PDF-` + taille ≤ 5 Mo, stocke dans `storage/cv/` avec nom aléatoire, retourne le nom).
- `Markdown::render(string $md): string` : HTML neutralisé (tout HTML source échappé, liens `http(s)`/`mailto`/relatifs uniquement, `rel="noopener noreferrer"`).
- `Mailer::send(string $to,string $subject,string $body,?string $replyTo=null): bool` (`mail()` best effort, jamais bloquant).
- `Migrator::run(): array` (migrations jouées), `Migrator::status()`.

### Fonctions globales (chargées par bootstrap, utilisables dans les vues)
`e($s)` échappement HTML · `t($key,$params=[])` · `t_html($key,$params=[])` (clés se terminant par `_html` uniquement) · `route($key,$params=[],$lang=null)` · `url($path)` (chemin interne → URL absolue avec base) · `asset($path)` · `vite_tags(string $entry)` (balises `<link>`/`<script type="module" nonce>` ; dev = serveur Vite si `VITE_DEV=1`, sinon `public/assets/.vite/manifest.json`) · `img_tag(string $slug,string $alt,string $sizes='100vw',array $attrs=[])` (lit `public/img/manifest.json`, produit `<img srcset>` avec `width/height`, `loading="lazy"` par défaut) · `loc(array $row,string $field)` (`$row[$field.'_'.lang]`, repli FR) · `csrf_field(string $form)` (champs `_token`, honeypot `website`) · `csrf_session_field()` · `csp_nonce()` · `old(string $k)` · `lang()` (`fr|en`) · `is_test()`.

### Contrats des formulaires publics (POST sans cookie)
Champs communs : `_token` (obligatoire), `website` (pot de miel, doit être vide), `consent` (case obligatoire). Rejet silencieux (réponse « envoyé ») si pot de miel rempli ; erreur « trop rapide » si délai < 3 s ; limite 5 envois/heure/IP hachée par formulaire. Succès : redirection 303 vers `?sent=1`.
- `join` : `first_name last_name email school level skills motivation availability`, fichier optionnel `cv` (PDF) → table `applications` (statut `new`).
- `contact` : `name email subject body` → `messages` (kind `contact`).
- `rights` : `name email request_type body` (`request_type` ∈ `access|rectify|erase|object|portability`) → `messages` (kind `rights`).

### Données passées aux vues publiques
Toutes les vues reçoivent `$meta` = `['title','description','canonical','robots','image','alternates']` et `$page` (clé de route). Spécifiques : `home` → `posts` (3 derniers publics), `partners` (publiés) ; `team` → `members` ; `partners` → `partnersByCategory` (catégorie → lignes) ; `news/index` → `posts`, `page`, `pages` ; `news/show` → `post`, `bodyHtml` ; `join|contact|rights` → `errors` (clé champ → clé lang), `sent` (bool), formulaire préremplie via `old()` ; pages légales → `org` (variables `ORG_*`, vides masquées). Pas de logique dans les vues au-delà de l'affichage.

## 5. Fichiers de langue
`app/lang/fr.php` et `en.php` retournent un tableau imbriqué. Feuilles = chaînes (texte brut). Listes = tableaux indexés d'objets/chaînes. Clés `*_html` : HTML de confiance limité à `<strong> <em> <a href> <br>`. Paramètres `:nom` identiques dans les deux langues. Racines : `meta nav common footer img home project team partners news join contact rights legal privacy cookies errors forms admin_public`. Les clés d'images : `img.<slug>` (texte alternatif de `public/img/<slug>-*.webp`). `docs/LANG-KEYS.md` donne les clés attendues par les vues. Contenu éditable en base (articles, équipe, partenaires) = champs `_fr/_en` + `seed-content.php`.

Tests automatiques (`php tests/run.php`) : parité des clés et des paramètres, toutes les clés `t('…')` utilisées dans `app/` existent, aucun « Arduino », compatibilité PHP 8.2.

## 6. Jetons CSS
`frontend/css/tokens.css` (figé) : couleurs `--c-*`, typo `--font-*` / `--fs-*`, espacements `--sp-*`, rayons, ombres, `--focus-ring`, largeur `--container`. Points de rupture mobile-first : `min-width: 48em` (768 px) et `80em` (1280 px). Contraste AA vérifié pour les paires documentées dans le fichier.

## 7. Modules de l'espace membres
Dossier `app/Modules/<Nom>/` : `module.php` retourne `['key','label','icon','route'=>'/admin/modules/<key>','roles'=>['admin','editor'],'controller'=>FQCN,'method'=>'index']`, vues dans `views/`. Découverte par glob dans `Admin\Router` ; l'entrée de menu apparaît automatiquement. Premier module : `Calculator` (modèle de McCormick, préréglage V6 **indicatif**).

## 8. Sécurité transverse
CSP `default-src 'self'; script-src 'self' 'nonce-…'; style-src 'self'; img-src 'self' data:; font-src 'self'; connect-src 'self'; object-src 'none'; base-uri 'none'; form-action 'self'; frame-ancestors 'none'` (ajouter l'origine Vite seulement si `VITE_DEV=1`). En-têtes : `X-Content-Type-Options`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy` vide, HSTS si https. `X-Robots-Tag: noindex` si `SITE_NOINDEX=1`.
