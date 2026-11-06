import path from 'path';
import type { Plugin, ViteDevServer } from 'vite';

import { isHookFile, writeManifest } from './helpers';

interface PluginOptions {
  dir: string;
  enableLogging?: boolean;
}

const name = 'vite-hook-manifest-plugin';

export default function HookManifestPlugin({
  dir,
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

      server.watcher.on('all', (event, file) => {
        if (!['add', 'unlink'].includes(event)) {
          return;
        }

        if (!file.startsWith(absoluteDir) || !isHookFile(file)) {
          return;
        }

        generate();

        if (enableLogging) {
          console.info(`[${name}][${event}] ${file}`);
        }
      });
    },
  };
}
