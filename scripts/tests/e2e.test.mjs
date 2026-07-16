// Tests de bout en bout sur un projet factice temporaire + serveur FTP jetable (aucun serveur réel).
// Lancer : node --test scripts/tests/   (nécessite PHP pour build-release)
import { test, before, after } from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { spawn, spawnSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';
import { startFakeFtp } from './fake-ftp.mjs';

const SCRIPTS = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
let tmp;
let proj;
let ftpRoot;

const write = (rel, content = '') => {
  const f = path.join(proj, rel);
  fs.mkdirSync(path.dirname(f), { recursive: true });
  fs.writeFileSync(f, content);
};
// spawn asynchrone obligatoire : le serveur FTP factice tourne dans ce même processus.
const run = (script, args = [], extraEnv = {}) =>
  new Promise((resolve) => {
    const p = spawn(process.execPath, [path.join(SCRIPTS, script), ...args], {
      env: { ...process.env, ABF_ROOT: proj, NO_COLOR: '1', ...extraEnv },
    });
    let stdout = '';
    let stderr = '';
    p.stdout.on('data', (d) => (stdout += d));
    p.stderr.on('data', (d) => (stderr += d));
    p.on('close', (status) => resolve({ status, stdout, stderr }));
  });
const out = (r) => `${r.stdout}\n${r.stderr}`;

before(() => {
  tmp = fs.mkdtempSync(path.join(os.tmpdir(), 'abf-e2e-'));
  proj = path.join(tmp, 'proj');
  ftpRoot = path.join(tmp, 'ftp');
  fs.mkdirSync(proj);
  fs.mkdirSync(path.join(ftpRoot, 'www', 'test'), { recursive: true });
  fs.mkdirSync(path.join(ftpRoot, 'www', 'autre-site'), { recursive: true });
  fs.writeFileSync(path.join(ftpRoot, 'www', 'index.php'), 'PROD');
  fs.writeFileSync(path.join(ftpRoot, 'www', 'test', '.env'), 'REMOTE_ENV_INTACT');
  spawnSync('git', ['init', '-q', proj]);
  spawnSync('git', ['-C', proj, 'config', 'user.email', 't@example.invalid']);
  spawnSync('git', ['-C', proj, 'config', 'user.name', 'test']);
  write('.gitignore', '.env\n.env.*\n!.env.example\n.deploy-state*.json\ndist/\n');
  write('.env.example', 'MAIL_TO=a@example.invalid\nORG_NAME=Org\nAPP_KEY=\n');
  write('app/Core/Foo.php', '<?php\ndeclare(strict_types=1);\nnamespace App\\Core;\nfinal class Foo {}\n');
  write('app/lang/fr.php', '<?php\nreturn [];\n');
  write('database/migrations/001_init.sql', 'CREATE TABLE {{PREFIX}}x ({{PK}});\n');
  write('database/migrate.php', '<?php\n');
  write('database/dev.sqlite', 'SQLITE-SECRET');
  write('public/index.php', '<?php\necho "ok";\n');
  write('public/.htaccess', 'Options -Indexes\n');
  write('public/hot', 'http://localhost:5173');
  write('public/uploads/.htaccess', 'php_flag engine off\n');
  write('public/uploads/photo.webp', 'IMG');
  write('public/assets/.vite/manifest.json', '{}');
  write('public/assets/app.js', 'console.log(1)');
  write('storage/.htaccess', 'Require all denied\n');
  write('storage/cv/leak.pdf', '%PDF-1');
  write('.htaccess', 'RewriteEngine On\n');
  write('.env', 'APP_KEY=' + 'f'.repeat(64) + '\n');
  write('node_modules/x/index.js', 'x');
  write('docs/a.md', 'doc');
  write('tests/t.php', '<?php\n');
  write('frontend/app.js', 'x');
});

after(() => fs.rmSync(tmp, { recursive: true, force: true }));

test('build-release : contenu, exclusions, RELEASE.json', async () => {
  const r = await run('build-release.mjs', ['--skip-vite']);
  assert.equal(r.status, 0, out(r));
  const dist = path.join(proj, 'dist');
  const has = (rel) => fs.existsSync(path.join(dist, rel));
  for (const ok of ['app/Core/Foo.php', 'app/lang/fr.php', 'database/migrations/001_init.sql', 'database/migrate.php', 'public/index.php',
    'public/assets/.vite/manifest.json', 'public/uploads/.htaccess', 'storage/.htaccess', 'storage/cv/.gitkeep', '.htaccess', 'RELEASE.json']) {
    assert.ok(has(ok), `devrait contenir ${ok}`);
  }
  for (const no of ['.env', 'database/dev.sqlite', 'public/hot', 'public/uploads/photo.webp', 'storage/cv/leak.pdf', 'node_modules', 'docs', 'tests', 'frontend', 'scripts']) {
    assert.ok(!has(no), `ne devrait pas contenir ${no}`);
  }
  const rel = JSON.parse(fs.readFileSync(path.join(dist, 'RELEASE.json'), 'utf8'));
  assert.ok(rel.date && 'commit' in rel);
  assert.match(r.stdout, /dist\/ prêt/);
});

test('build-release : refuse une erreur de syntaxe PHP, un secret et un manifest absent', async () => {
  write('app/Bad.php', '<?php\nfunction (\n');
  let r = await run('build-release.mjs', ['--skip-vite']);
  assert.notEqual(r.status, 0);
  assert.match(out(r), /php -l : app\/Bad\.php/);
  fs.rmSync(path.join(proj, 'app/Bad.php'));

  write('app/leak.txt', 'FTP_PASSWORD=supersecretvalue\n');
  r = await run('build-release.mjs', ['--skip-vite']);
  assert.notEqual(r.status, 0);
  assert.match(out(r), /secret : ligne de type \.env avec valeur dans app\/leak\.txt/);
  assert.ok(!out(r).includes('supersecretvalue'), 'la valeur du secret ne doit pas être affichée');
  fs.rmSync(path.join(proj, 'app/leak.txt'));

  fs.renameSync(path.join(proj, 'public/assets/.vite'), path.join(proj, 'public/assets/.vite-off'));
  r = await run('build-release.mjs', ['--skip-vite']);
  assert.notEqual(r.status, 0);
  assert.match(out(r), /manifest\.json/);
  fs.renameSync(path.join(proj, 'public/assets/.vite-off'), path.join(proj, 'public/assets/.vite'));

  assert.equal((await run('build-release.mjs', ['--skip-vite'])).status, 0);
});

test('extract-db-env : écrit .env.test sans afficher de valeur, refuse d\'écraser', async () => {
  const wp = path.join(tmp, 'fake-wp-config.php');
  fs.writeFileSync(wp, "<?php\ndefine('DB_NAME','base_fictive');\ndefine('DB_USER','user_fictif');\ndefine('DB_PASSWORD','MotDePasseFictif#42');\ndefine('DB_HOST','hote.fictif');\n");
  let r = await run('extract-db-env.mjs', ['--wp-config', wp]);
  assert.equal(r.status, 0, out(r));
  for (const v of ['base_fictive', 'user_fictif', 'MotDePasseFictif', 'hote.fictif']) assert.ok(!out(r).includes(v), `la valeur ${v} ne doit pas apparaître`);
  const env = fs.readFileSync(path.join(proj, '.env.test'), 'utf8');
  assert.match(env, /DB_PREFIX=abf_/);
  assert.match(env, /DB_PASS="?MotDePasseFictif#42"?/);
  r = await run('extract-db-env.mjs', ['--wp-config', wp]);
  assert.notEqual(r.status, 0);
  assert.match(out(r), /refus d'écraser/);
  assert.equal((await run('extract-db-env.mjs', ['--wp-config', wp, '--force'])).status, 0);
});

let ftp;
const deployEnv = (over = {}) => {
  const e = { FTP_HOST: '127.0.0.1', FTP_PORT: String(ftp.port), FTP_USER: 'u_fictif', FTP_PASSWORD: 'pw_fictif_xyz', FTP_SECURE: 'false', FTP_REMOTE_DIR: '/www/test', ...over };
  fs.writeFileSync(path.join(proj, '.env.deploy'), Object.entries(e).map(([k, v]) => `${k}=${v}`).join('\n') + '\n');
};
const deploy = (extra = []) => run('deploy.mjs', ['--env', 'test', '--allow-plain-ftp', ...extra]);
const remote = (rel) => path.join(ftpRoot, 'www', 'test', rel);

test('deploy : garde-fous (FTP_REMOTE_DIR dangereux refusés, FTP en clair refusé par défaut)', async () => {
  ftp = await startFakeFtp(ftpRoot);
  try {
    for (const bad of ['/www', '/www/', '/', '', '/home/x']) {
      deployEnv({ FTP_REMOTE_DIR: bad });
      const r = await deploy(['--dry-run']);
      assert.notEqual(r.status, 0, `devait refuser « ${bad} »`);
      assert.match(out(r), /FTP_REMOTE_DIR|sous-dossier|racine/);
    }
    deployEnv({ FTP_REMOTE_DIR: '/www/autre-site' });
    let r = await deploy(['--dry-run']);
    assert.notEqual(r.status, 0);
    assert.match(out(r), /i-know-what-i-am-doing/);
    assert.equal((await deploy(['--dry-run', '--i-know-what-i-am-doing'])).status, 0);
    deployEnv();
    r = await run('deploy.mjs', ['--env', 'test', '--dry-run']);
    assert.notEqual(r.status, 0);
    assert.match(out(r), /FTP en clair/);
    assert.equal(ftp.log.length, 0, 'aucune connexion FTP pendant les refus et les simulations');
  } finally {
    await ftp.close();
  }
});

test('deploy : dry-run ne touche à rien, puis envoi réel, création seule, reprise, suppression sûre', async () => {
  ftp = await startFakeFtp(ftpRoot, { failStorOnce: 'app/lang/fr.php' });
  try {
    deployEnv();
    // Simulation
    let r = await deploy(['--dry-run']);
    assert.equal(r.status, 0, out(r));
    assert.match(out(r), /\+ envoyer\s+app\/Core\/Foo\.php/);
    assert.ok(!fs.existsSync(remote('app')), 'dry-run : rien sur le serveur');
    assert.ok(!fs.existsSync(path.join(proj, '.deploy-state-test.json')), 'dry-run : pas d\'état');
    assert.ok(!out(r).includes('pw_fictif_xyz') && !out(r).includes('u_fictif'), 'aucun identifiant affiché');

    // Envoi réel (un échec transitoire simulé sur fr.php => nouvelle tentative)
    fs.mkdirSync(remote('storage'), { recursive: true });
    fs.writeFileSync(remote('storage/.htaccess'), 'HTACCESS_PROD_INTACT'); // existe déjà : ne doit pas être écrasé
    r = await deploy();
    assert.equal(r.status, 0, out(r));
    assert.match(out(r), /nouvelle tentative/);
    assert.ok(!out(r).includes('pw_fictif_xyz'));
    assert.equal(fs.readFileSync(remote('app/Core/Foo.php'), 'utf8').includes('final class Foo'), true);
    assert.equal(fs.readFileSync(remote('app/lang/fr.php'), 'utf8'), '<?php\nreturn [];\n');
    assert.ok(fs.existsSync(remote('public/uploads/.htaccess')), 'création seule : créé car absent');
    assert.equal(fs.readFileSync(remote('storage/.htaccess'), 'utf8'), 'HTACCESS_PROD_INTACT', 'création seule : jamais écrasé');
    assert.equal(fs.readFileSync(remote('.env'), 'utf8'), 'REMOTE_ENV_INTACT');
    assert.equal(fs.readFileSync(path.join(ftpRoot, 'www', 'index.php'), 'utf8'), 'PROD', 'racine /www intacte');
    assert.ok(!fs.existsSync(remote('database/dev.sqlite')) && !fs.existsSync(remote('public/uploads/photo.webp')));
    assert.ok(!ftp.log.some((l) => /^(STOR|DELE|MKD)\s+\/www\/[^t]/.test(l) || /^(STOR|DELE) \/www\/?$/.test(l)), 'aucune écriture hors /www/test');

    // Second passage : rien à faire (état par hash)
    r = await deploy();
    assert.equal(r.status, 0);
    assert.match(out(r), /Rien à faire/);

    // Modification + fichier retiré : seul le modifié est renvoyé, le retiré est supprimé
    write('app/Core/Foo.php', '<?php\ndeclare(strict_types=1);\nnamespace App\\Core;\nfinal class Foo { public int $v = 2; }\n');
    write('app/Extra.php', '<?php\n');
    assert.equal((await run('build-release.mjs', ['--skip-vite'])).status, 0);
    r = await deploy();
    assert.match(out(r), /3 envoy/);
    assert.ok(fs.existsSync(remote('app/Extra.php')));
    fs.rmSync(path.join(proj, 'app/Extra.php'));
    // un fichier distant non envoyé par le script ne doit jamais être supprimé
    fs.writeFileSync(remote('app/AjoutManuel.php'), 'manuel');
    fs.writeFileSync(remote('storage/cv/cv-candidat.pdf'), 'cv') ;
    assert.equal((await run('build-release.mjs', ['--skip-vite'])).status, 0);
    r = await deploy();
    assert.equal(r.status, 0, out(r));
    assert.ok(!fs.existsSync(remote('app/Extra.php')), 'fichier envoyé par le script puis retiré : supprimé');
    assert.ok(fs.existsSync(remote('app/AjoutManuel.php')), 'fichier inconnu du script : conservé');
    assert.ok(fs.existsSync(remote('storage/cv/cv-candidat.pdf')), 'storage jamais touché');
    assert.equal(fs.readFileSync(remote('app/Core/Foo.php'), 'utf8').includes('$v = 2'), true);
    const state = JSON.parse(fs.readFileSync(path.join(proj, '.deploy-state-test.json'), 'utf8'));
    assert.ok(!('app/Extra.php' in state.files) && 'app/Core/Foo.php' in state.files);
    assert.ok(!JSON.stringify(state).includes('pw_fictif_xyz'));
  } finally {
    await ftp.close();
  }
});

test('deploy : interruption puis reprise (l\'état n\'avance qu\'après un envoi réussi)', async () => {
  fs.rmSync(path.join(proj, '.deploy-state-test.json'), { force: true });
  fs.rmSync(path.join(ftpRoot, 'www', 'test', 'app'), { recursive: true, force: true });
  ftp = await startFakeFtp(ftpRoot);
  try {
    deployEnv({ FTP_PORT: '1' }); // port fermé : connexion impossible
    let r = await deploy();
    assert.notEqual(r.status, 0);
    assert.ok(!out(r).includes('pw_fictif_xyz'));
    assert.ok(!fs.existsSync(path.join(proj, '.deploy-state-test.json')), 'rien d\'envoyé => pas d\'état');
    deployEnv();
    r = await deploy();
    assert.equal(r.status, 0, out(r));
    assert.ok(fs.existsSync(remote('app/Core/Foo.php')));
  } finally {
    await ftp.close();
  }
});

test('push-env : garde-fous et envoi (noms de clés seulement)', async () => {
  ftp = await startFakeFtp(ftpRoot);
  try {
    deployEnv();
    // .env.test issu de extract-db-env (ignoré par git dans le projet factice)
    let r = await run('push-env.mjs', ['--env', 'test', '--allow-plain-ftp', '--dry-run']);
    assert.equal(r.status, 0, out(r));
    assert.match(out(r), /clés \(\d+\) : .*APP_KEY.*DB_PASS/);
    assert.ok(!out(r).includes('MotDePasseFictif'));

    // mod759_ refusé
    const envTest = path.join(proj, '.env.test');
    const good = fs.readFileSync(envTest, 'utf8');
    fs.writeFileSync(envTest, good.replace('DB_PREFIX=abf_', 'DB_PREFIX=mod759_'));
    r = await run('push-env.mjs', ['--env', 'test', '--allow-plain-ftp']);
    assert.notEqual(r.status, 0);
    assert.match(out(r), /mod759_/);
    fs.writeFileSync(envTest, good);

    // non ignoré par git refusé
    fs.writeFileSync(path.join(proj, '.gitignore'), 'dist/\n');
    r = await run('push-env.mjs', ['--env', 'test', '--allow-plain-ftp']);
    assert.notEqual(r.status, 0);
    assert.match(out(r), /pas ignoré par git/);
    fs.writeFileSync(path.join(proj, '.gitignore'), '.env\n.env.*\n!.env.example\ndist/\n');

    // dossier distant dangereux refusé
    deployEnv({ FTP_REMOTE_DIR: '/www' });
    r = await run('push-env.mjs', ['--env', 'test', '--allow-plain-ftp']);
    assert.notEqual(r.status, 0);
    deployEnv();

    // envoi réel
    r = await run('push-env.mjs', ['--env', 'test', '--allow-plain-ftp']);
    assert.equal(r.status, 0, out(r));
    assert.equal(fs.readFileSync(remote('.env'), 'utf8'), good);
    assert.ok(!out(r).includes('MotDePasseFictif'));
  } finally {
    await ftp.close();
  }
});
