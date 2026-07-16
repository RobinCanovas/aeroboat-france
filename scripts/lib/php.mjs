// Détection de PHP (PATH, WAMP, WinGet). Jamais de contournement : introuvable => code 1.
import fs from 'node:fs';
import path from 'node:path';
import os from 'node:os';
import { spawnSync } from 'node:child_process';

const IS_WIN = process.platform === 'win32';
export const TARGET_MINOR = '8.2';
export const INSTALL_HINT = IS_WIN
  ? 'winget install PHP.PHP.8.2'
  : 'sudo apt install php8.2-cli php8.2-sqlite3 php8.2-mbstring php8.2-gd   (ou le gestionnaire de paquets de votre système)';

/** Retourne "8.2.27" si `bin` est un PHP exécutable, sinon null. */
export function probeVersion(bin) {
  try {
    const r = spawnSync(bin, ['-r', 'echo PHP_VERSION;'], { encoding: 'utf8', timeout: 15000 });
    if (r.status !== 0) return null;
    const m = String(r.stdout).trim().match(/^\d+\.\d+\.\d+/);
    return m ? m[0] : null;
  } catch {
    return null;
  }
}

function listDirs(dir, re) {
  try {
    return fs
      .readdirSync(dir, { withFileTypes: true })
      .filter((e) => e.isDirectory() && re.test(e.name))
      .map((e) => path.join(dir, e.name))
      .sort()
      .reverse();
  } catch {
    return [];
  }
}

/** Candidats dans l'ordre : PHP_BIN, PATH, WAMP 8.2, WinGet. */
export function candidates() {
  const list = [];
  if (process.env.PHP_BIN) list.push(process.env.PHP_BIN);
  list.push('php'); // PATH
  if (IS_WIN) {
    const wampRoots = [process.env.WAMP_PATH, 'C:\\wamp64', 'D:\\wamp64'].filter(Boolean);
    for (const root of wampRoots) {
      for (const d of listDirs(path.join(root, 'bin', 'php'), /^php8\.2\./i)) list.push(path.join(d, 'php.exe'));
    }
    const local = process.env.LOCALAPPDATA || path.join(os.homedir(), 'AppData', 'Local');
    for (const d of listDirs(path.join(local, 'Microsoft', 'WinGet', 'Packages'), /^PHP\.PHP/i)) {
      list.push(path.join(d, 'php.exe'));
    }
    list.push(path.join(local, 'Microsoft', 'WinGet', 'Links', 'php.exe'));
  }
  return list;
}

/** Retourne { bin, version, warning|null } ou null si PHP est introuvable (sans quitter). */
export function findPhp() {
  for (const bin of candidates()) {
    if (path.isAbsolute(bin) && !fs.existsSync(bin)) continue;
    const version = probeVersion(bin);
    if (!version) continue;
    const warning = version.startsWith(`${TARGET_MINOR}.`)
      ? null
      : `PHP local ${version} différent de la cible OVH ${TARGET_MINOR} : pas de syntaxe 8.3+ (le contrôle de compatibilité de « npm test » protège).`;
    return { bin, version, warning };
  }
  return null;
}

/** Comme findPhp() mais quitte avec le code 1 et un message clair si PHP est introuvable. */
export function requirePhp({ quiet = false } = {}) {
  const php = findPhp();
  if (!php) {
    console.error('ERREUR PHP introuvable (ni dans le PATH, ni dans WAMP, ni dans WinGet).');
    console.error(`  Installez PHP ${TARGET_MINOR} :  ${INSTALL_HINT}`);
    console.error('  Puis rouvrez le terminal (variable PHP_BIN possible pour pointer vers un php précis).');
    process.exit(1);
  }
  if (php.warning && !quiet) console.warn(`ATTENTION ${php.warning}`);
  return php;
}
