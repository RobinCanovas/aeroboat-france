// Serveur FTP jetable (FTP en clair, mode passif) pour tester deploy.mjs sans serveur réel.
// Usage : const s = await startFakeFtp(rootDir, { failStorOnce: 'nom.txt' }); ... await s.close();
import net from 'node:net';
import fs from 'node:fs';
import path from 'node:path';

export async function startFakeFtp(root, { failStorOnce = null } = {}) {
  const log = []; // journal des commandes (sans le mot de passe)
  let failed = false;

  const resolveRemote = (cwd, p) => {
    const joined = p.startsWith('/') ? p : `${cwd.replace(/\/$/, '')}/${p}`;
    const parts = [];
    for (const seg of joined.split('/')) {
      if (seg === '' || seg === '.') continue;
      if (seg === '..') parts.pop();
      else parts.push(seg);
    }
    return '/' + parts.join('/');
  };
  const local = (remote) => path.join(root, ...remote.split('/').filter(Boolean));

  const server = net.createServer((sock) => {
    let cwd = '/';
    let dataServer = null;
    let dataSock = null;
    let dataWaiters = [];
    sock.setEncoding('utf8');
    const reply = (s) => sock.write(`${s}\r\n`);
    reply('220 fake ftp ready');

    const openPassive = () =>
      new Promise((resolve) => {
        if (dataServer) dataServer.close();
        dataSock = null;
        dataServer = net.createServer((ds) => {
          dataSock = ds;
          dataWaiters.splice(0).forEach((w) => w(ds));
        });
        dataServer.listen(0, '127.0.0.1', () => resolve(dataServer.address().port));
      });
    const getData = () => (dataSock ? Promise.resolve(dataSock) : new Promise((r) => dataWaiters.push(r)));

    let buf = '';
    sock.on('data', async (chunk) => {
      buf += chunk;
      let idx;
      while ((idx = buf.indexOf('\r\n')) >= 0) {
        const line = buf.slice(0, idx);
        buf = buf.slice(idx + 2);
        const sp = line.indexOf(' ');
        const cmd = (sp < 0 ? line : line.slice(0, sp)).toUpperCase();
        const arg = sp < 0 ? '' : line.slice(sp + 1);
        log.push(cmd === 'PASS' ? 'PASS ***' : line);
        try {
          switch (cmd) {
            case 'USER': reply('331 password please'); break;
            case 'PASS': reply('230 logged in'); break;
            case 'FEAT': reply('211-Features:\r\n SIZE\r\n UTF8\r\n211 End'); break;
            case 'OPTS': case 'TYPE': case 'STRU': case 'MODE': case 'NOOP': reply('200 ok'); break;
            case 'PWD': reply(`257 "${cwd}" is current directory`); break;
            case 'CWD': {
              const r = resolveRemote(cwd, arg);
              if (fs.existsSync(local(r)) && fs.statSync(local(r)).isDirectory()) { cwd = r; reply('250 ok'); } else reply('550 no such directory');
              break;
            }
            case 'CDUP': cwd = resolveRemote(cwd, '..'); reply('250 ok'); break;
            case 'MKD': {
              const r = resolveRemote(cwd, arg);
              fs.mkdirSync(local(r), { recursive: true });
              reply(`257 "${r}" created`);
              break;
            }
            case 'SIZE': {
              const r = resolveRemote(cwd, arg);
              if (fs.existsSync(local(r)) && fs.statSync(local(r)).isFile()) reply(`213 ${fs.statSync(local(r)).size}`); else reply('550 not found');
              break;
            }
            case 'DELE': {
              const r = resolveRemote(cwd, arg);
              if (fs.existsSync(local(r))) { fs.unlinkSync(local(r)); reply('250 deleted'); } else reply('550 not found');
              break;
            }
            case 'PASV': {
              const port = await openPassive();
              reply(`227 Entering Passive Mode (127,0,0,1,${port >> 8},${port & 255})`);
              break;
            }
            case 'EPSV': {
              const port = await openPassive();
              reply(`229 Entering Extended Passive Mode (|||${port}|)`);
              break;
            }
            case 'STOR': {
              const r = resolveRemote(cwd, arg);
              reply('150 go ahead');
              const ds = await getData();
              const chunks = [];
              ds.on('data', (d) => chunks.push(d));
              await new Promise((res) => ds.on('end', res).on('close', res));
              dataSock = null;
              if (failStorOnce && !failed && r.endsWith(failStorOnce)) {
                failed = true;
                reply('426 connection closed; transfer aborted');
                break;
              }
              fs.mkdirSync(path.dirname(local(r)), { recursive: true });
              fs.writeFileSync(local(r), Buffer.concat(chunks));
              reply('226 transfer complete');
              break;
            }
            case 'QUIT': reply('221 bye'); sock.end(); break;
            default: reply('502 not implemented');
          }
        } catch (e) {
          reply(`550 error ${e.message}`);
        }
      }
    });
    sock.on('error', () => {});
    sock.on('close', () => dataServer?.close());
  });

  await new Promise((r) => server.listen(0, '127.0.0.1', r));
  return {
    port: server.address().port,
    log,
    root,
    close: () => new Promise((r) => server.close(r)),
  };
}
