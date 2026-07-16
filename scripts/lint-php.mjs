// php -l sur app/ database/ public/ tests/ scripts/ — code retour 1 si une erreur ou si PHP est absent.
import { spawn } from 'node:child_process';
import path from 'node:path';
import { ROOT, walk, log, color } from './lib/common.mjs';
import { requirePhp } from './lib/php.mjs';

export const LINT_DIRS = ['app', 'database', 'public', 'tests', 'scripts'];
const SKIP_DIRS = new Set(['node_modules', 'vendor', '.git']);

export function collectPhpFiles(root = ROOT, dirs = LINT_DIRS) {
  const files = [];
  for (const d of dirs) {
    const abs = path.join(root, d);
    const found = walk(abs, {
      skip: (rel, ent) =>
        ent.isDirectory() &&
        (SKIP_DIRS.has(ent.name) || (d === 'public' && (rel === 'assets' || rel === 'uploads'))),
    }).filter((f) => f.endsWith('.php'));
    for (const f of found) files.push(`${d}/${f}`);
  }
  return files;
}

function lintOne(bin, root, rel) {
  return new Promise((resolve) => {
    const p = spawn(bin, ['-l', '-d', 'display_errors=1', '-d', 'log_errors=0', rel], { cwd: root });
    let out = '';
    p.stdout.on('data', (d) => (out += d));
    p.stderr.on('data', (d) => (out += d));
    p.on('error', (e) => resolve({ rel, ok: false, out: String(e.message) }));
    p.on('close', (code) => resolve({ rel, ok: code === 0, out: out.trim() }));
  });
}

export async function lintAll(bin, root, files, concurrency = 8) {
  const results = [];
  let i = 0;
  const worker = async () => {
    while (i < files.length) {
      const rel = files[i++];
      results.push(await lintOne(bin, root, rel));
    }
  };
  await Promise.all(Array.from({ length: Math.min(concurrency, files.length) }, worker));
  return results.sort((a, b) => a.rel.localeCompare(b.rel));
}

async function main() {
  const php = requirePhp();
  const files = collectPhpFiles();
  log.step(`php -l sur ${files.length} fichier(s) (PHP ${php.version})`);
  const results = await lintAll(php.bin, ROOT, files);
  const bad = results.filter((r) => !r.ok);
  for (const r of bad) {
    log.err(r.rel);
    for (const line of r.out.split(/\r?\n/).filter(Boolean)) console.error(`        ${line}`);
  }
  if (bad.length) {
    console.error(color.red(`${bad.length} fichier(s) en erreur sur ${files.length}.`));
    process.exit(1);
  }
  log.ok(`${files.length} fichier(s) PHP sans erreur de syntaxe.`);
}

import { fileURLToPath } from 'node:url';
if (process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url)) {
  main();
}
