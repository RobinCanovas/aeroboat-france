// npm run release : construit dist/ prêt pour FTP.
//   node scripts/build-release.mjs [--skip-vite]   (--skip-vite : réutilise public/assets existant, pour les tests)
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { spawnSync } from 'node:child_process';
import { ROOT, parseArgs, walk, log, die, formatBytes, gitInfo } from './lib/common.mjs';
import { requirePhp } from './lib/php.mjs';
import { collectPhpFiles, lintAll } from './lint-php.mjs';

const SKELETON_DIRS = ['cv', 'logs', 'sessions'];
/** Fichiers indispensables : leur absence rend le build inutilisable ou dangereux. */
export const REQUIRED = [
  '.htaccess',
  'public/index.php',
  'public/.htaccess',
  'storage/.htaccess',
  'public/assets/.vite/manifest.json',
];

/** Noms jamais copiés dans dist, où qu'ils soient. */
export function isExcludedName(name) {
  return (
    name === '.git' ||
    name === 'node_modules' ||
    name === '.DS_Store' ||
    name === 'Thumbs.db' ||
    name === 'hot' ||
    name === '.env' ||
    name.startsWith('.env.') ||
    name === '.env.deploy' ||
    /\.sqlite(-.*)?$/i.test(name) ||
    /\.log$/i.test(name) ||
    /^\.deploy-state/.test(name)
  );
}

const SECRET_PATTERNS = [
  [/-----BEGIN (?:RSA |EC |OPENSSH |DSA )?PRIVATE KEY-----/, 'clé privée'],
  [/^\s*(?:FTP_PASSWORD|DB_PASS|DB_PASSWORD|APP_KEY|SETUP_TOKEN)\s*=\s*[^\s#"']{4,}/m, 'ligne de type .env avec valeur'],
  [/define\s*\(\s*['"]DB_PASSWORD['"]/, 'constante wp-config DB_PASSWORD'],
];
const BINARY_EXT = /\.(webp|png|jpe?g|gif|avif|ico|woff2?|ttf|otf|eot|pdf|zip|gz|mp4|webm)$/i;
const FORBIDDEN_FILE = /(^|\/)(\.env(\..*)?|wp-config\.php|.*\.sqlite(-.*)?|.*\.pem|id_rsa.*)$/i;

/** Retourne les problèmes de sécurité trouvés dans dist (noms de fichiers et contenus). */
export function scanSecrets(distDir) {
  const problems = [];
  for (const rel of walk(distDir)) {
    if (FORBIDDEN_FILE.test(rel) && rel !== '.env.example') problems.push(`fichier interdit : ${rel}`);
    if (BINARY_EXT.test(rel)) continue;
    const abs = path.join(distDir, rel);
    if (fs.statSync(abs).size > 2 * 1024 * 1024) continue;
    const text = fs.readFileSync(abs, 'utf8');
    for (const [re, label] of SECRET_PATTERNS) {
      if (re.test(text)) problems.push(`${label} dans ${rel}`);
    }
  }
  return problems;
}

function copyTree(srcRoot, destRoot, rel, filter) {
  const src = path.join(srcRoot, rel);
  if (!fs.existsSync(src)) return;
  fs.cpSync(src, path.join(destRoot, rel), {
    recursive: true,
    filter: (s) => {
      if (isExcludedName(path.basename(s))) return false;
      return filter ? filter(path.relative(srcRoot, s).replace(/\\/g, '/')) : true;
    },
  });
}

/** Copie le contenu à déployer de `root` vers `dist` (sans vite). */
export function assemble(root, dist) {
  fs.rmSync(dist, { recursive: true, force: true });
  fs.mkdirSync(dist, { recursive: true });

  copyTree(root, dist, 'app');
  copyTree(root, dist, 'database/migrations');
  for (const f of fs.existsSync(path.join(root, 'database')) ? fs.readdirSync(path.join(root, 'database')) : []) {
    if (f.endsWith('.php')) {
      fs.mkdirSync(path.join(dist, 'database'), { recursive: true });
      fs.copyFileSync(path.join(root, 'database', f), path.join(dist, 'database', f));
    }
  }
  // public/ : sans uploads (hors .htaccess/.gitkeep) ni hot.
  copyTree(root, dist, 'public', (rel) => {
    if (rel.startsWith('public/uploads/')) {
      const base = rel.split('/').pop();
      // les dossiers ne sont gardés que s'ils sont vides de contenu : on ne copie que le squelette
      return base === '.htaccess' || base === '.gitkeep' || fs.statSync(path.join(root, rel)).isDirectory();
    }
    return true;
  });
  // Les sous-dossiers d'uploads copiés mais sans squelette restent vides : on les retire.
  pruneEmptyDirs(path.join(dist, 'public', 'uploads'));
  fs.mkdirSync(path.join(dist, 'public', 'uploads'), { recursive: true });

  // storage/ : squelette uniquement (.htaccess + dossiers vides marqués .gitkeep), jamais de contenu.
  fs.mkdirSync(path.join(dist, 'storage'), { recursive: true });
  const storageHt = path.join(root, 'storage', '.htaccess');
  if (fs.existsSync(storageHt)) fs.copyFileSync(storageHt, path.join(dist, 'storage', '.htaccess'));
  for (const d of SKELETON_DIRS) {
    fs.mkdirSync(path.join(dist, 'storage', d), { recursive: true });
    fs.writeFileSync(path.join(dist, 'storage', d, '.gitkeep'), '');
    const ht = path.join(root, 'storage', d, '.htaccess');
    if (fs.existsSync(ht)) fs.copyFileSync(ht, path.join(dist, 'storage', d, '.htaccess'));
  }
  if (fs.existsSync(path.join(root, '.htaccess'))) fs.copyFileSync(path.join(root, '.htaccess'), path.join(dist, '.htaccess'));
}

function pruneEmptyDirs(dir) {
  if (!fs.existsSync(dir)) return;
  for (const ent of fs.readdirSync(dir, { withFileTypes: true })) {
    if (ent.isDirectory()) pruneEmptyDirs(path.join(dir, ent.name));
  }
  if (fs.readdirSync(dir).length === 0) fs.rmdirSync(dir);
}

function dirSize(dist) {
  let total = 0;
  let count = 0;
  for (const rel of walk(dist)) {
    total += fs.statSync(path.join(dist, rel)).size;
    count++;
  }
  return { total, count };
}

async function main() {
  const args = parseArgs(process.argv.slice(2));
  const php = requirePhp();
  const dist = path.join(ROOT, 'dist');
  if (path.resolve(dist) === path.resolve(ROOT) || path.basename(dist) !== 'dist') die('Chemin dist invalide.');

  if (args['skip-vite'] === true) {
    log.warn('--skip-vite : réutilisation de public/assets tel quel.');
  } else {
    log.step('vite build');
    const viteBin = path.join(ROOT, 'node_modules', 'vite', 'bin', 'vite.js');
    if (!fs.existsSync(viteBin)) die('Vite introuvable : lancez « npm install » (ou rattachez node_modules).');
    const r = spawnSync(process.execPath, [viteBin, 'build'], { cwd: ROOT, stdio: 'inherit' });
    if (r.status !== 0) die('vite build a échoué.');
  }

  log.step('Assemblage de dist/');
  assemble(ROOT, dist);

  log.step('Vérifications');
  const errors = [];
  for (const rel of REQUIRED) {
    if (!fs.existsSync(path.join(dist, rel))) errors.push(`fichier requis absent du build : ${rel}`);
  }
  errors.push(...scanSecrets(dist).map((p) => `secret : ${p}`));

  const phpFiles = collectPhpFiles(dist, ['app', 'database', 'public']);
  const results = await lintAll(php.bin, dist, phpFiles);
  for (const r of results.filter((x) => !x.ok)) errors.push(`php -l : ${r.rel}\n        ${r.out.replace(/\r?\n/g, '\n        ')}`);
  if (!results.some((r) => !r.ok)) log.ok(`php -l : ${phpFiles.length} fichier(s) PHP valides`);

  if (errors.length) {
    for (const e of errors) log.err(e);
    die(`Release invalide (${errors.length} problème(s)) : dist/ ne doit pas être déployé.`);
  }
  log.ok('aucun secret, fichiers requis présents (dont public/assets/.vite/manifest.json)');

  const git = gitInfo(ROOT);
  const { total, count } = dirSize(dist);
  fs.writeFileSync(
    path.join(dist, 'RELEASE.json'),
    JSON.stringify({ date: new Date().toISOString(), commit: git.hash, dirty: git.dirty, files: count + 1 }, null, 2) + '\n',
  );
  log.ok(`dist/ prêt : ${count + 1} fichiers, ${formatBytes(total)} (commit ${git.hash ?? 'inconnu'}${git.dirty ? ' + modifications locales' : ''})`);
}

if (process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url)) {
  main().catch((e) => die(String(e?.message ?? e)));
}
