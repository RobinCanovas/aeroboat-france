// Connexion FTP(S) partagée par deploy.mjs et push-env.mjs. Les identifiants ne sont jamais affichés.
import fs from 'node:fs';
import path from 'node:path';
import { Client } from 'basic-ftp';
import { ROOT, readEnvFile, log } from './common.mjs';
import { validateRemoteDir, GuardError } from './deploy-core.mjs';

export const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

/**
 * Lit `.env.deploy` (sans jamais l'afficher) et retourne la configuration validée.
 * Lève GuardError / Error avec un message sans secret.
 */
export function loadDeployConfig({ root = ROOT, envName, allowOther = false, allowPlain = false, needCredentials = true } = {}) {
  if (envName !== 'test') {
    throw new GuardError(`Environnement « ${envName ?? ''} » non géré : seul --env test est autorisé.`);
  }
  const file = path.join(root, '.env.deploy');
  if (!fs.existsSync(file)) {
    throw new Error(
      '.env.deploy introuvable à la racine du projet. Créez-le (voir README « Fichiers que vous créez vous-même »), il est ignoré par git.',
    );
  }
  const e = readEnvFile(file);
  const remoteDir = validateRemoteDir(e.FTP_REMOTE_DIR, { allowOther });
  const secure = String(e.FTP_SECURE ?? 'true').toLowerCase() !== 'false';
  if (!secure && !allowPlain) {
    throw new GuardError('FTP_SECURE=false (FTP en clair) refusé : utilisez FTPS, ou ajoutez --allow-plain-ftp en connaissance de cause.');
  }
  if (needCredentials) {
    const missing = ['FTP_HOST', 'FTP_USER', 'FTP_PASSWORD'].filter((k) => !e[k]);
    if (missing.length) throw new Error(`.env.deploy : clé(s) manquante(s) ou vide(s) : ${missing.join(', ')}.`);
  }
  return {
    host: e.FTP_HOST || '',
    port: e.FTP_PORT ? Number(e.FTP_PORT) : 21,
    user: e.FTP_USER || '',
    password: e.FTP_PASSWORD || '',
    secure,
    insecureTls: String(e.FTP_INSECURE_TLS ?? '').toLowerCase() === 'true',
    remoteDir,
  };
}

/** Ouvre une session FTPS explicite (certificat vérifié sauf FTP_INSECURE_TLS=true). */
export async function connect(cfg) {
  const client = new Client(60_000);
  client.ftp.verbose = false;
  await client.access({
    host: cfg.host,
    port: cfg.port,
    user: cfg.user,
    password: cfg.password,
    secure: cfg.secure ? true : false,
    secureOptions: cfg.secure ? { rejectUnauthorized: !cfg.insecureTls, servername: cfg.host } : undefined,
  });
  return client;
}

/** Message d'erreur sans fuite d'identifiants. */
export function safeMessage(err, cfg) {
  let m = String(err?.message ?? err);
  for (const s of [cfg.password, cfg.user]) {
    if (s) m = m.split(s).join('***');
  }
  return m;
}

/**
 * Exécute `fn(client)` avec nouvelles tentatives et temporisation exponentielle.
 * `session` = { client, cfg } : en cas d'échec la connexion est rouverte.
 */
export async function withRetry(session, fn, { tries = 4, baseMs = 1000, label = 'opération' } = {}) {
  let lastErr;
  for (let attempt = 1; attempt <= tries; attempt++) {
    try {
      if (!session.client || session.client.closed) session.client = await connect(session.cfg);
      return await fn(session.client);
    } catch (err) {
      lastErr = err;
      try {
        session.client?.close();
      } catch {
        /* ignoré */
      }
      session.client = null;
      session.dirs?.clear?.();
      if (attempt < tries) {
        const wait = baseMs * 2 ** (attempt - 1);
        log.warn(`${label} : échec (${safeMessage(err, session.cfg)}), nouvelle tentative dans ${wait} ms (${attempt}/${tries - 1})`);
        await sleep(wait);
      }
    }
  }
  throw new Error(`${label} : abandon après ${tries} tentatives (${safeMessage(lastErr, session.cfg)})`);
}
