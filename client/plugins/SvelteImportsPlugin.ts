import type { Plugin, ViteDevServer } from 'vite';
import fs, { Dirent } from 'fs';
import path from 'path';
import chokidar from 'chokidar';

interface FileInfo {
  path: string;
  name: string;
  extension?: string;
}

function* walkSync(dir: string): Generator<FileInfo> {
  const entries: Dirent[] = fs.readdirSync(dir, { withFileTypes: true });

  for (const entry of entries) {
    const fullPath = path.join(dir, entry.name);

    if (entry.isDirectory()) {
      yield* walkSync(fullPath);
    } else if (entry.isFile()) {
      const extension = path.extname(entry.name);
      const name = path.basename(entry.name, extension);

      if (name !== 'index') {
        yield { path: dir, name, extension };
      }
    }
  }
}

async function createIndexFile(dir: string): Promise<string> {
  const files: FileInfo[] = Array.from(walkSync(dir));

  const indexContent: string = files.map(file => {
    let relativePath: string = path.relative(dir, path.join(file.path, file.name));

    if(relativePath.startsWith('/')) {
      relativePath = relativePath.slice(1);
    }

    return file.extension === '.svelte'
      ? `export { default as ${file.name} } from './${relativePath}${file.extension}';`
      : `export * from './${relativePath}';`;
  }).join('\n');

  const output = path.join(dir, 'index.ts');
  fs.writeFileSync(output, indexContent);

  return output;
}

interface PluginOptions {
  dirs: string[],
  libraryMode?: boolean,
  usePolling?: boolean,
  enableLogging?: boolean
}

function flattenDirectories(dirs: string[]): string[] {
  return dirs.reduce((acc: string[], dir: string) => {
    const subDirs = fs.readdirSync(dir, { withFileTypes: true })
      .filter((dirent) => dirent.isDirectory())
      .map((dirent) => `${dir}/${dirent.name}`);
    return acc.concat(subDirs);
  }, []);
}

function findIndexDir(file: string): string|undefined {
  let dir = path.relative(process.cwd(), path.dirname(file));

  while (dir !== path.dirname(dir)) {
    if (fs.existsSync(path.join(dir, 'package.json')) || dir === '/') {
      return;
    }

    if (fs.existsSync(path.join(dir, 'index.ts'))) {
      break;
    }

    dir = path.dirname(dir);
  }

  return dir;
}

const name = 'vite-svelte-imports-plugin';

function createRunner(modules: string[], {enableLogging = false}: Partial<PluginOptions>) {
  const isRunning: Map<string, boolean> = new Map();

  for(const path of modules) {
    isRunning.set(path, false);
  }

  return async (file: string): Promise<boolean> => {
    const dir = findIndexDir(file);

    if(!dir) return false;

    if (!modules.includes(dir)) {
      return false;
    }

    if(isRunning.get(dir)) {
      return false;
    }

    isRunning.set(dir, true);
    const path = await createIndexFile(dir);
    isRunning.set(dir, false)

    if (enableLogging) {
      console.info(`[${name}] Created index file: ${path}`);
    }

    return true;
  }
}

function createWatcher(dirs: string[], usePolling: boolean) {
  return chokidar.watch(dirs, {
    ignoreInitial: true,
    usePolling,
    ignored: /index\.ts$/
  });
}

export default function SvelteImportsPlugin({ dirs, libraryMode, usePolling, enableLogging }: PluginOptions): Plugin {
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
      const absoluteDirs = modules.map((dir:string) => path.resolve(dir));

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

          if(file.endsWith('index.ts')) {
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
    }
  };
}