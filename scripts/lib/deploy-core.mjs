// Logique pure du déploiement (garde-fous, état par hash, plan) : testable sans réseau.
import fs from 'node:fs';
import path from 'node:path';
import { sha256File } from './common.mjs';

export const DEFAULT_REMOTE_DIR = '/www/test';
export const STATE_VERSION = 1;

/** Fichiers en « création seule » : envoyés s'ils n'existent pas, jamais écrasés ni supprimés. */
export const CREATE_ONLY = new Set(['storage/.htaccess', 'public/uploads/.htaccess']);

/** Squelette autorisé en création seule dans les dossiers protégés (permet de créer les dossiers). */
const SKELETON_NAMES = new Set(['.htaccess', '.gitkeep']);

/** Préfixes jamais touchés par une suppression ni par un écrasement. */
export const PROTECTED_PREFIXES = ['storage/', 'public/uploads/'];
export const PROTECTED_FILES = new Set(['.env']);

export class GuardError extends Error {}

/**
 * Valide et normalise FTP_REMOTE_DIR. Lève GuardError si dangereux.
 * Règles : non vide, pas `/`, `/www`, `/www/`, doit être un sous-dossier de `/www/` ;
 * tout autre nom que `/www/test` exige `allowOther` (--i-know-what-i-am-doing).
 */
export function validateRemoteDir(input, { allowOther = false } = {}) {
  if (typeof input !== 'string' || input.trim() === '') {
    throw new GuardError('FTP_REMOTE_DIR est vide : refus de déployer.');
  }
  const raw = input.trim().replace(/\\/g, '/');
  if (raw.split('/').includes('..') || raw.includes('\0')) {
    throw new GuardError('FTP_REMOTE_DIR contient « .. » : refus.');
  }
  const norm = '/' + raw.split('/').filter((s) => s !== '' && s !== '.').join('/');
  if (norm === '/' || norm === '/www') {
    throw new GuardError(`FTP_REMOTE_DIR="${input}" désigne la racine du site de production : refus catégorique.`);
  }
  if (!norm.startsWith('/www/')) {
    throw new GuardError(`FTP_REMOTE_DIR="${input}" n'est pas un sous-dossier de /www/ : refus.`);
  }
  if (norm !== DEFAULT_REMOTE_DIR && !allowOther) {
    throw new GuardError(
      `FTP_REMOTE_DIR="${norm}" n'est pas ${DEFAULT_REMOTE_DIR} : ajoutez --i-know-what-i-am-doing pour confirmer.`,
    );
  }
  return norm;
}

/** Jointure distante sûre : le résultat reste obligatoirement sous remoteDir. */
export function remoteJoin(remoteDir, rel) {
  const parts = rel.replace(/\\/g, '/').split('/');
  if (rel === '' || parts.some((p) => p === '' || p === '.' || p === '..') || rel.startsWith('/')) {
    throw new GuardError(`Chemin relatif invalide : "${rel}".`);
  }
  return `${remoteDir}/${parts.join('/')}`;
}

export function isProtected(rel) {
  if (PROTECTED_FILES.has(rel) || rel.startsWith('.env')) return true;
  return PROTECTED_PREFIXES.some((p) => rel.startsWith(p));
}

/** Politique d'un fichier du build : 'sync' | 'create-only' | 'skip'. */
export function policyFor(rel) {
  if (CREATE_ONLY.has(rel)) return 'create-only';
  if (rel.startsWith('.env')) return 'skip';
  if (isProtected(rel)) {
    // Dans storage/ et public/uploads/ : uniquement le squelette, en création seule.
    return SKELETON_NAMES.has(rel.split('/').pop()) ? 'create-only' : 'skip';
  }
  return 'sync';
}

/** Une suppression n'est permise que si le script a envoyé le fichier ET qu'il n'est pas protégé. */
export function isDeletable(rel, state) {
  return Object.prototype.hasOwnProperty.call(state.files, rel) && !isProtected(rel);
}

export function emptyState(remoteDir) {
  return { version: STATE_VERSION, remoteDir, files: {}, created: {} };
}

export function stateFileFor(root, envName) {
  return path.join(root, `.deploy-state-${envName}.json`);
}

/** Charge l'état ; s'il vise un autre dossier distant, il est ignoré (aucune suppression possible). */
export function loadState(file, remoteDir) {
  let data;
  try {
    data = JSON.parse(fs.readFileSync(file, 'utf8'));
  } catch {
    return { state: emptyState(remoteDir), note: null };
  }
  if (!data || data.version !== STATE_VERSION || data.remoteDir !== remoteDir || typeof data.files !== 'object') {
    return {
      state: emptyState(remoteDir),
      note: 'État précédent ignoré (autre dossier distant ou format différent) : tout sera renvoyé, aucune suppression.',
    };
  }
  data.created = data.created && typeof data.created === 'object' ? data.created : {};
  return { state: data, note: null };
}

/** Écriture atomique (tmp + rename) pour survivre à une interruption. */
export function saveState(file, state) {
  const tmp = `${file}.tmp`;
  fs.writeFileSync(tmp, JSON.stringify(state, null, 2) + '\n');
  fs.renameSync(tmp, file);
}

/** Table { chemin relatif: sha256 } des fichiers de dist/ soumis à la politique 'sync'/'create-only'. */
export function scanLocal(distDir, files) {
  const out = {};
  for (const rel of files) {
    const pol = policyFor(rel);
    if (pol === 'skip') continue;
    out[rel] = { hash: sha256File(path.join(distDir, rel)), policy: pol };
  }
  return out;
}

/** Ordre d'envoi : assets et code d'abord, points d'entrée en dernier. */
function uploadRank(rel) {
  if (rel === 'public/index.php') return 3;
  if (rel === '.htaccess' || rel === 'public/.htaccess') return 2;
  if (rel === 'RELEASE.json') return 4;
  return 1;
}

/**
 * Calcule le plan : { upload: [{rel,hash,policy}], createOnly: [...], remove: [rel], unchanged: n }.
 * `local` vient de scanLocal ; `state` de loadState.
 */
export function plan(local, state) {
  const upload = [];
  const createOnly = [];
  let unchanged = 0;
  for (const [rel, info] of Object.entries(local)) {
    if (info.policy === 'create-only') {
      if (!state.created[rel]) createOnly.push({ rel, ...info });
      else unchanged++;
    } else if (state.files[rel] === info.hash) {
      unchanged++;
    } else {
      upload.push({ rel, ...info });
    }
  }
  upload.sort((a, b) => uploadRank(a.rel) - uploadRank(b.rel) || a.rel.localeCompare(b.rel));
  createOnly.sort((a, b) => a.rel.localeCompare(b.rel));
  const remove = Object.keys(state.files)
    .filter((rel) => !(rel in local) && isDeletable(rel, state))
    .sort();
  return { upload, createOnly, remove, unchanged };
}
