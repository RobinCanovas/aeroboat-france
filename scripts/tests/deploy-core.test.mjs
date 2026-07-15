// Tests unitaires de la logique de déploiement. Lancer : node --test scripts/tests/
import { test } from 'node:test';
import assert from 'node:assert/strict';
import {
  validateRemoteDir,
  GuardError,
  remoteJoin,
  policyFor,
  isDeletable,
  plan,
  emptyState,
} from '../lib/deploy-core.mjs';
import { parseEnv } from '../lib/common.mjs';
import { extractWpConstants, fmtEnvValue, buildEnvTest } from '../extract-db-env.mjs';
import { checkEnvTest } from '../push-env.mjs';

test('FTP_REMOTE_DIR : refus des valeurs dangereuses', () => {
  for (const bad of ['', '   ', undefined, '/', '/www', '/www/', '//www//', '/www/..', '/www/../etc', '/home/x', 'test', '/wwwx/test', '\\www']) {
    assert.throws(() => validateRemoteDir(bad), GuardError, `devait refuser ${JSON.stringify(bad)}`);
    assert.throws(() => validateRemoteDir(bad, { allowOther: true }), GuardError, `refus même avec le drapeau : ${JSON.stringify(bad)}`);
  }
});

test('FTP_REMOTE_DIR : /www/test accepté, autre sous-dossier exige le drapeau', () => {
  assert.equal(validateRemoteDir('/www/test'), '/www/test');
  assert.equal(validateRemoteDir('/www/test/'), '/www/test');
  assert.equal(validateRemoteDir('\\www\\test'), '/www/test');
  assert.throws(() => validateRemoteDir('/www/autre'), /i-know-what-i-am-doing/);
  assert.equal(validateRemoteDir('/www/autre', { allowOther: true }), '/www/autre');
});

test('remoteJoin reste sous le dossier distant', () => {
  assert.equal(remoteJoin('/www/test', 'public/index.php'), '/www/test/public/index.php');
  for (const bad of ['../x', 'a/../../b', '/etc/passwd', '', 'a//b', './a']) {
    assert.throws(() => remoteJoin('/www/test', bad), GuardError, bad);
  }
});

test('politiques : storage, uploads, .env', () => {
  assert.equal(policyFor('storage/.htaccess'), 'create-only');
  assert.equal(policyFor('public/uploads/.htaccess'), 'create-only');
  assert.equal(policyFor('storage/cv/.gitkeep'), 'create-only');
  assert.equal(policyFor('storage/cv/secret.pdf'), 'skip');
  assert.equal(policyFor('public/uploads/photo.webp'), 'skip');
  assert.equal(policyFor('.env'), 'skip');
  assert.equal(policyFor('.env.test'), 'skip');
  assert.equal(policyFor('app/Core/Db.php'), 'sync');
});

test('suppression : seulement les fichiers envoyés par le script, jamais protégés', () => {
  const state = emptyState('/www/test');
  state.files['app/Old.php'] = 'h1';
  state.files['storage/cv/x.pdf'] = 'h2'; // état corrompu à la main
  state.files['.env'] = 'h3';
  assert.equal(isDeletable('app/Old.php', state), true);
  assert.equal(isDeletable('app/Inconnu.php', state), false);
  assert.equal(isDeletable('storage/cv/x.pdf', state), false);
  assert.equal(isDeletable('public/uploads/a.webp', state), false);
  assert.equal(isDeletable('.env', state), false);
  const p = plan({}, state);
  assert.deepEqual(p.remove, ['app/Old.php']);
});

test('plan : hash identique = inchangé, création seule une fois, points d\'entrée en dernier', () => {
  const state = emptyState('/www/test');
  state.files['app/a.php'] = 'AAA';
  state.created['storage/.htaccess'] = true;
  const local = {
    'app/a.php': { hash: 'AAA', policy: 'sync' },
    'app/b.php': { hash: 'BBB', policy: 'sync' },
    'public/index.php': { hash: 'III', policy: 'sync' },
    '.htaccess': { hash: 'HHH', policy: 'sync' },
    'storage/.htaccess': { hash: 'S', policy: 'create-only' },
    'public/uploads/.htaccess': { hash: 'U', policy: 'create-only' },
  };
  const p = plan(local, state);
  assert.deepEqual(p.upload.map((f) => f.rel), ['app/b.php', '.htaccess', 'public/index.php']);
  assert.deepEqual(p.createOnly.map((f) => f.rel), ['public/uploads/.htaccess']);
  assert.equal(p.unchanged, 2);
  assert.deepEqual(p.remove, []);
});

test('parseEnv : commentaires en fin de ligne, guillemets, échappements', () => {
  const e = parseEnv('A=1 # note\nB="x # y \\"z\\" \\\\"\nC=\nexport D=ok\n# E=no\n');
  assert.equal(e.A, '1');
  assert.equal(e.B, 'x # y "z" \\');
  assert.equal(e.C, '');
  assert.equal(e.D, 'ok');
  assert.equal('E' in e, false);
});

test('extract-db-env : lecture des define() PHP, aller-retour .env', () => {
  const php = `<?php
define( 'DB_NAME', 'ma_base' );
define('DB_USER', "mon_user");
define('DB_PASSWORD', 'p@ss\\'w "o#rd\\\\x');
define('DB_HOST', 'localhost');
`;
  const { values, missing } = extractWpConstants(php);
  assert.deepEqual(missing, []);
  assert.equal(values.DB_PASSWORD, 'p@ss\'w "o#rd\\x');
  const env = buildEnvTest(values, 'MAIL_TO=a@b.fr\nORG_NAME=Aéroboat France\nORG_PHONE=+33 7 82\nDB_PASS=ignore\n');
  const back = parseEnv(env);
  assert.equal(back.DB_PASS, values.DB_PASSWORD);
  assert.equal(back.DB_NAME, 'ma_base');
  assert.equal(back.DB_PREFIX, 'abf_');
  assert.equal(back.DB_DRIVER, 'mysql');
  assert.equal(back.APP_URL, 'https://aeroboat-france.com/test');
  assert.equal(back.APP_ENV, 'test');
  assert.equal(back.SITE_NOINDEX, '1');
  assert.match(back.APP_KEY, /^[0-9a-f]{64}$/);
  assert.match(back.SETUP_TOKEN, /^[0-9a-f]{32}$/);
  assert.equal(back.ORG_PHONE, '+33 7 82');
  assert.equal(fmtEnvValue('simple_1.2'), 'simple_1.2');
});

test('extract-db-env : constante manquante signalée', () => {
  const { missing } = extractWpConstants("<?php define('DB_NAME','x');");
  assert.deepEqual(missing, ['DB_USER', 'DB_PASSWORD', 'DB_HOST']);
});

test('push-env : refuse mod759_, fichier non ignoré, APP_KEY absente', () => {
  const ok = 'APP_ENV=test\nAPP_KEY=' + 'a'.repeat(64) + '\nDB_PREFIX=abf_\n';
  assert.deepEqual(checkEnvTest(ok, true).errors, []);
  assert.equal(checkEnvTest(ok.replace('abf_', 'mod759_'), true).errors.length, 1);
  assert.equal(checkEnvTest(ok, false).errors.length, 1);
  assert.equal(checkEnvTest('APP_ENV=test\n', true).errors.length, 1);
  assert.deepEqual(checkEnvTest(ok, true).keys, ['APP_ENV', 'APP_KEY', 'DB_PREFIX']);
});
