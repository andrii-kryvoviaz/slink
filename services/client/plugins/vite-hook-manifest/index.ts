import path from 'path';
import type { Plugin, ViteDevServer } from 'vite';

import { createWatcher, isHookFile, writeManifest } from './helpers';

interface PluginOptions {
  dir: string;
  usePolling?: boolean;
  enableLogging?: boolean;
}

const name = 'vite-hook-manifest-plugin';

export default function HookManifestPlugin({
  dir,
  usePolling,
  enableLogging,
}: PluginOptions): Plugin {
  const generate = (): void => {
    const { output, changed } = writeManifest(dir);

    if (enableLogging && changed) {
      console.info(`[${name}] Created manifest file: ${output}`);
    }
  };

  return {
    name,
    buildStart() {
      generate();
    },
    configureServer(server: ViteDevServer) {
      const absoluteDir = path.resolve(dir);

      try {
        const watcher = usePolling
          ? createWatcher(absoluteDir, usePolling)
          : server.watcher;

        watcher.on('all', (event, file) => {
          if (!['add', 'unlink'].includes(event)) {
            return;
          }

          if (!file.startsWith(absoluteDir) || !isHookFile(file)) {
            return;
          }

          if (event === 'unlink' && usePolling) {
            watcher.unwatch(file);
          }

          generate();

          if (enableLogging) {
            console.info(`[${name}][${event}] ${file}`);
          }
        });
      } catch {
        console.error(`[${name}] Failed to create file watcher`);
      }
    },
  };
}
