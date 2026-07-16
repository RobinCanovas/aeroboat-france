// Pousse .env.test vers <FTP_REMOTE_DIR>/.env (mêmes garde-fous que deploy.mjs). N'affiche que les NOMS des clés.
//   node scripts/push-env.mjs --env test [--dry-run] [--i-know-what-i-am-doing] [--allow-plain-ftp]
import fs from 'node:fs';
import path from 'node:path';
import { Readable } from 'node:stream';
import { fileURLToPath } from 'node:url';
import { ROOT, parseArgs, parseEnv, isGitIgnored, log, die } from './lib/common.mjs';
import { remoteJoin } from './lib/deploy-core.mjs';
import { loadDeployConfig, withRetry, safeMessage } from './lib/ftp.mjs';

const args = parseArgs(process.argv.slice(2));

/** Contrôles locaux sur .env.test : retourne la liste des erreurs (messages sans valeur). */
export function checkEnvTest(text, ignored) {
  const errors = [];
  const env = parseEnv(text);
  if (!ignored) errors.push('.env.test n\'est pas ignoré par git (git check-ignore) : refus.');
  if (/^\s*DB_PREFIX\s*=\s*["']?mod759_/m.test(text)) {
    errors.push('.env.test contient DB_PREFIX=mod759_ (tables WordPress de production) : refus.');
  }
  if (!env.APP_KEY || env.APP_KEY.length < 32) errors.push('APP_KEY absente ou trop courte.');
  if (env.APP_ENV && env.APP_ENV !== 'test') errors.push('APP_ENV doit valoir « test » dans .env.test.');
  return { errors, keys: Object.keys(env) };
}

async function main() {
  if (args.env !== 'test') die('Usage : node scripts/push-env.mjs --env test [--dry-run]');
  const dryRun = args['dry-run'] === true;

  let cfg;
  try {
    cfg = loadDeployConfig({
      envName: args.env,
      allowOther: args['i-know-what-i-am-doing'] === true,
      allowPlain: args['allow-plain-ftp'] === true,
      needCredentials: !dryRun,
    });
  } catch (e) {
    die(e.message);
  }

  const file = path.join(ROOT, '.env.test');
  if (!fs.existsSync(file)) die('.env.test introuvable : lancez d\'abord « node scripts/extract-db-env.mjs ».');
  const text = fs.readFileSync(file, 'utf8');
  const { errors, keys } = checkEnvTest(text, isGitIgnored('.env.test'));
  if (errors.length) {
    for (const m of errors) log.err(m);
    process.exit(1);
  }

  const remote = remoteJoin(cfg.remoteDir, '.env');
  log.step(`${dryRun ? '[SIMULATION] ' : ''}Envoi de .env.test vers ${remote}`);
  log.info(`  clés (${keys.length}) : ${keys.join(', ')}`);
  if (dryRun) {
    log.ok('Simulation terminée : rien n\'a été envoyé.');
    return;
  }

  const session = { cfg, client: null, dirs: new Set() };
  try {
    await withRetry(
      session,
      async (c) => {
        await c.ensureDir(cfg.remoteDir);
        await c.uploadFrom(Readable.from([Buffer.from(text, 'utf8')]), remote);
      },
      { label: 'envoi du .env' },
    );
  } catch (e) {
    die(safeMessage(e, cfg));
  }
  session.client?.close();
  log.ok('.env poussé (le fichier distant est protégé par .htaccess : vérifiez qu\'il n\'est pas servi en HTTP).');
}

if (process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url)) {
  main().catch((e) => die(String(e?.message ?? e)));
}
