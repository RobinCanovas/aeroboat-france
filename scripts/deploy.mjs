// Déploiement FTP(S) de dist/ vers /www/test/ — état par hash, suppression sûre, garde-fous prod.
//   node scripts/deploy.mjs --env test [--dry-run] [--i-know-what-i-am-doing] [--allow-plain-ftp]
// FTP_INSECURE_TLS=true (dans .env.deploy) désactive la vérification du certificat : jamais par défaut.
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { ROOT, parseArgs, walk, log, die, color, formatBytes } from './lib/common.mjs';
import {
  GuardError,
  scanLocal,
  loadState,
  saveState,
  stateFileFor,
  plan as makePlan,
  remoteJoin,
  isDeletable,
} from './lib/deploy-core.mjs';
import { loadDeployConfig, connect, withRetry, safeMessage } from './lib/ftp.mjs';

const args = parseArgs(process.argv.slice(2));
const dryRun = args['dry-run'] === true;

function remoteDirOf(rel) {
  const i = rel.lastIndexOf('/');
  return i < 0 ? '' : rel.slice(0, i);
}

async function ensureRemoteDir(session, cfg, relDir) {
  const abs = relDir === '' ? cfg.remoteDir : remoteJoin(cfg.remoteDir, relDir);
  if (session.dirs.has(abs)) return;
  await withRetry(session, (c) => c.ensureDir(abs), { label: `création du dossier ${relDir || '/'}` });
  session.dirs.add(abs);
}

async function remoteExists(session, abs) {
  return withRetry(
    session,
    async (c) => {
      try {
        await c.size(abs);
        return true;
      } catch (err) {
        if (err && err.code === 550) return false; // fichier absent
        throw err;
      }
    },
    { label: 'vérification distante' },
  );
}

async function main() {
  if (args.env !== 'test') die('Usage : node scripts/deploy.mjs --env test [--dry-run]');

  let cfg;
  try {
    cfg = loadDeployConfig({
      envName: args.env,
      allowOther: args['i-know-what-i-am-doing'] === true,
      allowPlain: args['allow-plain-ftp'] === true,
      needCredentials: !dryRun,
    });
  } catch (e) {
    die(e instanceof GuardError || e instanceof Error ? e.message : String(e));
  }

  const dist = path.join(ROOT, 'dist');
  if (!fs.existsSync(path.join(dist, 'RELEASE.json'))) {
    die('dist/ absent ou incomplet : lancez d\'abord « npm run release ».');
  }
  const files = walk(dist);
  const local = scanLocal(dist, files);
  const stateFile = stateFileFor(ROOT, args.env);
  const { state, note } = loadState(stateFile, cfg.remoteDir);
  if (note) log.warn(note);
  const p = makePlan(local, state);

  log.step(`${dryRun ? '[SIMULATION] ' : ''}Déploiement vers ${cfg.remoteDir} (${cfg.secure ? 'FTPS explicite' : 'FTP EN CLAIR'})`);
  if (cfg.insecureTls) log.warn('FTP_INSECURE_TLS=true : certificat TLS non vérifié.');
  log.info(`  à envoyer : ${p.upload.length}   création seule : ${p.createOnly.length}   à supprimer : ${p.remove.length}   inchangés : ${p.unchanged}`);

  if (dryRun) {
    for (const f of p.upload) log.info(`  + envoyer    ${f.rel}`);
    for (const f of p.createOnly) log.info(`  ~ créer si absent (jamais écrasé)  ${f.rel}`);
    for (const rel of p.remove) log.info(`  - supprimer  ${rel}`);
    log.ok('Simulation terminée : rien n\'a été envoyé ni supprimé.');
    return;
  }

  if (p.upload.length + p.createOnly.length + p.remove.length === 0) {
    log.ok('Rien à faire : le serveur est à jour.');
    return;
  }

  const session = { cfg, client: null, dirs: new Set() };
  session.client = await connect(cfg).catch((e) => die(`Connexion impossible : ${safeMessage(e, cfg)}`));
  const counts = { sent: 0, created: 0, skipped: 0, removed: 0 };
  let bytes = 0;
  try {
    for (const f of p.createOnly) {
      const abs = remoteJoin(cfg.remoteDir, f.rel);
      await ensureRemoteDir(session, cfg, remoteDirOf(f.rel));
      if (await remoteExists(session, abs)) {
        counts.skipped++;
      } else {
        await withRetry(session, (c) => c.uploadFrom(path.join(dist, f.rel), abs), { label: `envoi ${f.rel}` });
        counts.created++;
      }
      state.created[f.rel] = true;
      saveState(stateFile, state);
    }

    let n = 0;
    for (const f of p.upload) {
      const abs = remoteJoin(cfg.remoteDir, f.rel);
      await ensureRemoteDir(session, cfg, remoteDirOf(f.rel));
      await withRetry(session, (c) => c.uploadFrom(path.join(dist, f.rel), abs), { label: `envoi ${f.rel}` });
      state.files[f.rel] = f.hash; // mis à jour seulement après un envoi réussi
      saveState(stateFile, state);
      counts.sent++;
      bytes += fs.statSync(path.join(dist, f.rel)).size;
      if (++n % 25 === 0 || n === p.upload.length) log.info(color.dim(`  … ${n}/${p.upload.length} fichiers`));
    }

    for (const rel of p.remove) {
      if (!isDeletable(rel, state)) continue; // double sécurité
      const abs = remoteJoin(cfg.remoteDir, rel);
      await withRetry(session, (c) => c.remove(abs, true), { label: `suppression ${rel}` });
      delete state.files[rel];
      saveState(stateFile, state);
      counts.removed++;
    }
  } catch (e) {
    log.err(safeMessage(e, cfg));
    log.err('Déploiement interrompu. L\'état est conservé : relancez la même commande pour reprendre.');
    session.client?.close();
    process.exit(1);
  }
  session.client?.close();
  log.ok(`Terminé : ${counts.sent} envoyé(s) (${formatBytes(bytes)}), ${counts.created} créé(s) en création seule, ${counts.skipped} déjà présent(s), ${counts.removed} supprimé(s).`);
}

if (process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url)) {
  main().catch((e) => die(String(e?.message ?? e)));
}
