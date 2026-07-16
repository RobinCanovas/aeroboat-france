// Utilitaires partagés par les scripts (Node 20+, ESM, Windows + Linux).
import { fileURLToPath } from 'node:url';
import path from 'node:path';
import fs from 'node:fs';
import crypto from 'node:crypto';
import { spawnSync } from 'node:child_process';

/** Racine du projet (surchargeable pour les tests via ABF_ROOT). */
export const ROOT = process.env.ABF_ROOT
  ? path.resolve(process.env.ABF_ROOT)
  : path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..', '..');

const useColor = process.stdout.isTTY && !process.env.NO_COLOR;
const c = (code) => (s) => (useColor ? `\x1b[${code}m${s}\x1b[0m` : String(s));
export const color = { red: c(31), green: c(32), yellow: c(33), cyan: c(36), dim: c(2), bold: c(1) };

export const log = {
  info: (m) => console.log(m),
  step: (m) => console.log(color.cyan(`==> ${m}`)),
  ok: (m) => console.log(color.green(`  OK   ${m}`)),
  warn: (m) => console.warn(color.yellow(`  ATTENTION ${m}`)),
  err: (m) => console.error(color.red(`  ERREUR ${m}`)),
};

/** Termine le processus avec un message d'erreur clair. */
export function die(message, code = 1) {
  log.err(message);
  process.exit(code);
}

/** Analyse minimale d'arguments : --flag, --cle valeur, --cle=valeur. */
export function parseArgs(argv) {
  const out = { _: [] };
  for (let i = 0; i < argv.length; i++) {
    const a = argv[i];
    if (!a.startsWith('--')) {
      out._.push(a);
      continue;
    }
    const eq = a.indexOf('=');
    if (eq > 0) {
      out[a.slice(2, eq)] = a.slice(eq + 1);
    } else if (argv[i + 1] !== undefined && !argv[i + 1].startsWith('--')) {
      out[a.slice(2)] = argv[++i];
    } else {
      out[a.slice(2)] = true;
    }
  }
  return out;
}

/**
 * Analyse d'un contenu .env : KEY=VALUE, commentaires `#` (aussi en fin de ligne
 * après un espace), valeurs entre guillemets doubles (échappements \" et \\) ou simples.
 * Ne journalise jamais de valeur.
 */
export function parseEnv(text) {
  const env = {};
  for (const raw of text.replace(/^﻿/, '').split(/\r?\n/)) {
    const line = raw.trim();
    if (line === '' || line.startsWith('#')) continue;
    const m = line.match(/^(?:export\s+)?([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*)$/);
    if (!m) continue;
    let v = m[2];
    if (v.startsWith('"')) {
      let out = '';
      let i = 1;
      for (; i < v.length; i++) {
        if (v[i] === '\\' && (v[i + 1] === '"' || v[i + 1] === '\\')) {
          out += v[++i];
        } else if (v[i] === '"') {
          break;
        } else {
          out += v[i];
        }
      }
      v = out;
    } else if (v.startsWith("'")) {
      const end = v.indexOf("'", 1);
      v = end > 0 ? v.slice(1, end) : v.slice(1);
    } else {
      v = v.replace(/\s+#.*$/, '').trim();
    }
    env[m[1]] = v;
  }
  return env;
}

export function readEnvFile(file) {
  return parseEnv(fs.readFileSync(file, 'utf8'));
}

export function randomHex(bytes = 32) {
  return crypto.randomBytes(bytes).toString('hex');
}

export function formatBytes(n) {
  if (n < 1024) return `${n} o`;
  if (n < 1024 * 1024) return `${(n / 1024).toFixed(1)} Kio`;
  return `${(n / 1024 / 1024).toFixed(2)} Mio`;
}

/** Liste récursive de fichiers (chemins relatifs POSIX), triée. */
export function walk(dir, { skip = () => false } = {}) {
  const out = [];
  const rec = (abs, rel) => {
    for (const ent of fs.readdirSync(abs, { withFileTypes: true })) {
      const r = rel ? `${rel}/${ent.name}` : ent.name;
      if (skip(r, ent)) continue;
      if (ent.isDirectory()) rec(path.join(abs, ent.name), r);
      else if (ent.isFile()) out.push(r);
    }
  };
  if (fs.existsSync(dir)) rec(dir, '');
  return out.sort();
}

export function sha256File(file) {
  return crypto.createHash('sha256').update(fs.readFileSync(file)).digest('hex');
}

/** Hash git court de HEAD (ou null), avec indicateur de modifications locales. */
export function gitInfo(root = ROOT) {
  const run = (args) => spawnSync('git', ['-C', root, ...args], { encoding: 'utf8' });
  const h = run(['rev-parse', '--short', 'HEAD']);
  if (h.status !== 0) return { hash: null, dirty: false };
  const s = run(['status', '--porcelain']);
  return { hash: h.stdout.trim(), dirty: s.status === 0 && s.stdout.trim() !== '' };
}

/** `git check-ignore -q` : true si le fichier est ignoré par git (faux si pas de dépôt git). */
export function isGitIgnored(rel, root = ROOT) {
  const r = spawnSync('git', ['-C', root, 'check-ignore', '-q', rel], { encoding: 'utf8' });
  return r.status === 0;
}

/** Appelle `fn` une seule fois sur SIGINT/SIGTERM/SIGBREAK. */
export function onShutdown(fn) {
  let done = false;
  const handler = () => {
    if (done) return;
    done = true;
    fn();
  };
  process.on('SIGINT', handler);
  process.on('SIGTERM', handler);
  process.on('SIGBREAK', handler);
  return handler;
}

/** Tue un processus enfant et ses descendants (Windows : taskkill /T). */
export function killTree(child) {
  if (!child || child.exitCode !== null || child.signalCode !== null) return;
  if (process.platform === 'win32') {
    spawnSync('taskkill', ['/pid', String(child.pid), '/T', '/F'], { stdio: 'ignore' });
  } else {
    try {
      child.kill('SIGTERM');
    } catch {
      /* déjà terminé */
    }
  }
}
