import path from 'path';
import type { Plugin, ViteDevServer } from 'vite';

import {
  createIndexFile,
  createWatcher,
  findIndexDir,
  flattenDirectories,
} from './helpers';

interface PluginOptions {
  dirs: string[];
  libraryMode?: boolean;
  usePolling?: boolean;
  enableLogging?: boolean;
}

const name = 'vite-svelte-imports-plugin';

function createRunner(
  modules: string[],
  { enableLogging = false }: Partial<PluginOptions>
) {
  const isRunning: Map<string, boolean> = new Map();

  for (const path of modules) {
    isRunning.set(path, false);
  }

  return async (file: string): Promise<boolean> => {
    const dir = findIndexDir(file);

    if (!dir) return false;

    if (!modules.includes(dir)) {
      return false;
    }

    if (isRunning.get(dir)) {
      return false;
    }

    isRunning.set(dir, true);
    const path = await createIndexFile(dir);
    isRunning.set(dir, false);

    if (enableLogging) {
      console.info(`[${name}] Created index file: ${path}`);
    }

    return true;
  };
}

export default function SvelteImportsPlugin({
  dirs,
  libraryMode,
  usePolling,
  enableLogging,
}: PluginOptions): Plugin {
  const modules = libraryMode ? dirs : flattenDirectories(dirs);
  const run = createRunner(modules, { enableLogging });

  return {
    name,
    async buildStart() {
      for (const dir of modules) {
        const path = await createIndexFile(dir);

        if (enableLogging) {
          console.info(`[${name}] Created index file: ${path}`);
        }
      }
    },
    async configureServer(server: ViteDevServer) {
      const absoluteDirs = modules.map((dir: string) => path.resolve(dir));

      // use chokidar directly instead of ViteDevServer to be able to set polling
      // since docker volumes might not propagate unlink event correctly
      // vite implementation does not use polling by default
      try {
        const watcher = usePolling
          ? createWatcher(absoluteDirs, usePolling)
          : server.watcher;

        watcher.on('all', async (event, file) => {
          if (!['add', 'unlink'].includes(event)) {
            return;
          }

          if (file.endsWith('index.ts')) {
            return;
          }

          if (event === 'unlink' && usePolling) {
            watcher.unwatch(file);
          }

          const result = await run(file);

          if (enableLogging && result) {
            console.info(`[${name}][${event}] ${file}`);
          }
        });
      } catch (error) {}
    },
  };
}
