// npm run dev : Vite (HMR) + serveur PHP intégré (SQLite local). Arrêt propre des deux processus.
import fs from 'node:fs';
import path from 'node:path';
import { spawn, spawnSync } from 'node:child_process';
import { ROOT, log, die, randomHex, readEnvFile, killTree, onShutdown } from './lib/common.mjs';
import { requirePhp } from './lib/php.mjs';

const HOST = '127.0.0.1';
const PORT = process.env.PORT || '8000';

/** Contenu du .env de démonstration : aucun secret réel, clés aléatoires, SQLite. */
export function demoEnv() {
  return [
    '# .env local de démonstration généré par `npm run dev` (ignoré par git, aucun secret réel).',
    'APP_ENV=local',
    'APP_DEBUG=1',
    `APP_URL=http://${HOST}:${PORT}`,
    `APP_KEY=${randomHex(32)}`,
    `SETUP_TOKEN=${randomHex(16)}`,
    'SITE_NOINDEX=1',
    'VITE_DEV=1',
    'DB_DRIVER=sqlite',
    'DB_PATH=database/dev.sqlite',
    'DB_PREFIX=abf_',
    'MAIL_TO=',
    'ORG_NAME=Aéroboat France',
    '',
  ].join('\n');
}

function run(php, args, label) {
  const r = spawnSync(php, args, { cwd: ROOT, stdio: 'inherit', env: process.env });
  if (r.status !== 0) die(`${label} a échoué (code ${r.status}).`);
}

function main() {
  const php = requirePhp();
  const viteBin = path.join(ROOT, 'node_modules', 'vite', 'bin', 'vite.js');
  if (!fs.existsSync(viteBin)) die('Vite introuvable : lancez « npm install » (ou rattachez node_modules).');
  const envFile = path.join(ROOT, '.env');

  if (!fs.existsSync(envFile)) {
    fs.writeFileSync(envFile, demoEnv(), { mode: 0o600 });
    log.ok('.env local de démonstration créé (SQLite, clés aléatoires).');
  }
  const env = readEnvFile(envFile);
  const isSqlite = (env.DB_DRIVER || 'sqlite') === 'sqlite';

  // Premier lancement : migration + seed si la base SQLite n'existe pas encore.
  if (isSqlite) {
    const dbFile = path.resolve(ROOT, env.DB_PATH || 'database/dev.sqlite');
    if (!fs.existsSync(dbFile)) {
      log.step('Première exécution : migration de la base SQLite locale');
      run(php.bin, ['database/migrate.php'], 'La migration');
      if (fs.existsSync(path.join(ROOT, 'database', 'seed.php'))) {
        run(php.bin, ['database/seed.php'], 'Le seed');
      }
    }
  }

  const children = [];
  let stopping = false;
  const stop = (code = 0) => {
    if (stopping) return;
    stopping = true;
    for (const c of children) killTree(c);
    setTimeout(() => process.exit(code), 150);
  };

  log.step(`PHP  : http://${HOST}:${PORT}   (Ctrl+C pour tout arrêter)`);
  const phpProc = spawn(php.bin, ['-S', `${HOST}:${PORT}`, '-t', 'public', 'scripts/dev-router.php'], {
    cwd: ROOT,
    stdio: 'inherit',
    env: { ...process.env, VITE_DEV: '1' },
  });
  children.push(phpProc);

  log.step('Vite : serveur de développement des assets');
  const viteProc = spawn(process.execPath, [viteBin], {
    cwd: ROOT,
    stdio: 'inherit',
    env: { ...process.env, VITE_DEV: '1' },
  });
  children.push(viteProc);

  for (const [name, proc] of [['PHP', phpProc], ['Vite', viteProc]]) {
    proc.on('error', (e) => {
      log.err(`${name} : ${e.message}`);
      stop(1);
    });
    proc.on('exit', (code, signal) => {
      if (stopping) return;
      log.warn(`${name} s'est arrêté (${signal ?? `code ${code}`}) : arrêt de l'autre processus.`);
      stop(code || 1);
    });
  }
  onShutdown(() => stop(0));
}

import { fileURLToPath } from 'node:url';
if (process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url)) {
  main();
}
