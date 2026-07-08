import { defineConfig } from 'vite';
import { existsSync, writeFileSync, rmSync } from 'node:fs';
import { resolve, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = dirname(fileURLToPath(import.meta.url));
const hotFile = resolve(root, 'public/hot');

// Entrées : main.js toujours ; admin.js seulement si le module « cms-admin » l'a écrit.
const input = { main: resolve(root, 'frontend/main.js') };
if (existsSync(resolve(root, 'frontend/admin.js'))) {
  input.admin = resolve(root, 'frontend/admin.js');
}

// Écrit public/hot (lu par vite_tags() quand VITE_DEV=1) tant que le serveur de dev tourne.
function hotFilePlugin() {
  const cleanup = () => { try { rmSync(hotFile, { force: true }); } catch { /* ignore */ } };
  return {
    name: 'abf-hot-file',
    apply: 'serve',
    configureServer(server) {
      server.httpServer?.once('listening', () => {
        const addr = server.httpServer.address();
        const host = server.config.server.host;
        const h = typeof host === 'string' && host !== '0.0.0.0' ? host : '127.0.0.1';
        const port = typeof addr === 'object' && addr ? addr.port : server.config.server.port;
        writeFileSync(hotFile, `http://${h}:${port}`);
      });
      server.httpServer?.once('close', cleanup);
      process.once('exit', cleanup);
      process.once('SIGINT', () => { cleanup(); process.exit(0); });
    },
  };
}

export default defineConfig(({ command }) => ({
  root,
  // Build : chemins relatifs (le site vit sous /test/ en recette) ; dev : racine du serveur Vite.
  base: command === 'build' ? './' : '/',
  publicDir: false,
  plugins: [hotFilePlugin()],
  server: {
    host: '127.0.0.1',
    port: 5173,
    strictPort: true,
    cors: true,
  },
  build: {
    outDir: 'public/assets',
    emptyOutDir: true,
    manifest: true,
    sourcemap: false,
    cssCodeSplit: true,
    assetsInlineLimit: 0,
    rollupOptions: { input },
  },
}));
