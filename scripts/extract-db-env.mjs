// Génère .env.test à partir des identifiants MySQL de wp-config.php, SANS jamais afficher une valeur.
//   node scripts/extract-db-env.mjs [--wp-config <chemin>] [--force]
// Défaut : ../SiTE/_/www/wp-config.php (relatif à la racine du projet). Refuse d'écraser sans --force.
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { ROOT, parseArgs, parseEnv, randomHex, isGitIgnored, log, die } from './lib/common.mjs';

const WP_KEYS = ['DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_HOST'];

function unescapePhp(s, quote) {
  if (quote === "'") return s.replace(/\\(['\\])/g, '$1');
  return s.replace(/\\(["\\$])/g, '$1');
}

/** Extrait les constantes define('DB_…', '…') ; retourne { values, missing }. Aucune valeur n'est journalisée. */
export function extractWpConstants(php) {
  const values = {};
  for (const key of WP_KEYS) {
    const re = new RegExp(
      `define\\s*\\(\\s*(['"])${key}\\1\\s*,\\s*(?:'((?:\\\\.|[^'\\\\])*)'|"((?:\\\\.|[^"\\\\])*)")\\s*\\)`,
    );
    const m = php.match(re);
    if (!m) continue;
    values[key] = m[2] !== undefined ? unescapePhp(m[2], "'") : unescapePhp(m[3], '"');
  }
  const missing = WP_KEYS.filter((k) => values[k] === undefined || values[k] === '');
  return { values, missing };
}

/** Valeur .env : brute si sûre, sinon entre guillemets doubles (échappements \" et \\). */
export function fmtEnvValue(v) {
  if (/^[A-Za-z0-9_.@:/+,=%-]*$/.test(v)) return v;
  return `"${v.replace(/\\/g, '\\\\').replace(/"/g, '\\"')}"`;
}

/** Construit le contenu de .env.test. `example` = contenu de .env.example (pour ORG_*, MAIL_*). */
export function buildEnvTest(values, exampleText = '') {
  const ex = parseEnv(exampleText);
  const lines = [
    '# Généré par scripts/extract-db-env.mjs — NE PAS COMMITER (ignoré par git).',
    'APP_ENV=test',
    'APP_DEBUG=0',
    'APP_URL=https://aeroboat-france.com/test',
    `APP_KEY=${randomHex(32)}`,
    `SETUP_TOKEN=${randomHex(16)}`,
    'SITE_NOINDEX=1',
    'VITE_DEV=0',
    '',
    'DB_DRIVER=mysql',
    `DB_HOST=${fmtEnvValue(values.DB_HOST)}`,
    `DB_NAME=${fmtEnvValue(values.DB_NAME)}`,
    `DB_USER=${fmtEnvValue(values.DB_USER)}`,
    `DB_PASS=${fmtEnvValue(values.DB_PASSWORD)}`,
    'DB_PREFIX=abf_',
    '',
  ];
  for (const k of ['MAIL_TO', 'MAIL_FROM']) {
    if (k in ex) lines.push(`${k}=${fmtEnvValue(ex[k])}`);
  }
  lines.push('');
  for (const [k, v] of Object.entries(ex)) {
    if (k.startsWith('ORG_')) lines.push(`${k}=${fmtEnvValue(v)}`);
  }
  return lines.join('\n') + '\n';
}

function main() {
  const args = parseArgs(process.argv.slice(2));
  const wpPath = path.resolve(ROOT, typeof args['wp-config'] === 'string' ? args['wp-config'] : '../SiTE/_/www/wp-config.php');
  const out = path.join(ROOT, '.env.test');

  if (fs.existsSync(out) && args.force !== true) {
    die('.env.test existe déjà : refus d\'écraser (ajoutez --force).');
  }
  if (!fs.existsSync(wpPath)) die(`wp-config.php introuvable : ${wpPath}`);

  const { values, missing } = extractWpConstants(fs.readFileSync(wpPath, 'utf8'));
  if (missing.length) die(`Constante(s) absente(s) ou vide(s) dans wp-config.php : ${missing.join(', ')}.`);

  const examplePath = path.join(ROOT, '.env.example');
  const example = fs.existsSync(examplePath) ? fs.readFileSync(examplePath, 'utf8') : '';
  const content = buildEnvTest(values, example);
  fs.writeFileSync(out, content, { mode: 0o600 });

  const keys = Object.keys(parseEnv(content));
  log.ok(`.env.test écrit (${keys.length} clés : ${keys.join(', ')}). Aucune valeur affichée.`);
  if (!isGitIgnored('.env.test')) log.warn('.env.test n\'est pas ignoré par git : ne le committez pas, corrigez .gitignore.');
  log.info('Étape suivante : npm run push-env  (après avoir créé .env.deploy).');
}

if (process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url)) {
  main();
}
