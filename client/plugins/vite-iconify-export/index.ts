import path from 'path';
import type { Plugin, ViteDevServer } from 'vite';

import { walkSync } from '../dependencies/walkDirectory';
import { IconFile } from './lib/IconFile';

interface PluginOptions {
  dir: string;
  extensions?: string[];
  exclude?: string[];
}

const defaultOptions: PluginOptions = {
  dir: 'src',
  extensions: ['.svelte', '.ts'],
  exclude: ['index.ts', 'theme.icons.ts', 'theme.default.ts'],
};

const name = 'vite-iconify-export-plugin';

function createRunner(iconsFile: IconFile) {
  return async (file: string): Promise<void> => {
    iconsFile.processTemplate(path.resolve(file));
  };
}

export default function IconifyExportPlugin({
  dir,
  extensions,
  exclude,
}: PluginOptions = defaultOptions): Plugin {
  const iconsFile = IconFile.create(path.resolve(dir, 'theme.icons.ts'));
  const run = createRunner(iconsFile);

  return {
    name,
    async buildStart() {
      const files = Array.from(walkSync(dir));
      for (const { extension, path: filePath, name } of files) {
        if (extension && extensions?.includes(extension)) {
          await run(path.join(filePath, `${name}${extension}`));
        }
      }
      iconsFile.persist();
    },
    async configureServer(server: ViteDevServer) {
      server.watcher.on('all', async (event, file) => {
        if (!['change'].includes(event)) {
          return;
        }

        const extension = path.extname(file);
        const fileName = path.basename(file);

        if (extensions?.includes(extension) && !exclude?.includes(fileName)) {
          await run(file);
          iconsFile.persist();
        }
      });
    },
  };
}
