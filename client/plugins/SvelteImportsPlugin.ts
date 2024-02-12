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

async function createIndexFile(dir: string) {
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

  fs.writeFileSync(path.join(dir, 'index.ts'), indexContent);
}

interface PluginOptions {
  dirs: string[],
  libraryMode?: boolean,
  usePolling?: boolean,
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

function createRunner(modules: string[]) {
  const isRunning: Map<string, boolean> = new Map();

  for(const path of modules) {
    isRunning.set(path, false);
  }

  return async (file: string) => {
    const dir = findIndexDir(file);

    if(!dir) return;

    if (modules.includes(dir)) {
      if(isRunning.get(dir)) {
        return;
      }

      isRunning.set(dir, true);

      await createIndexFile(dir);

      isRunning.set(dir, false)
    }
  }
}

export default function SvelteImportsPlugin({ dirs, libraryMode, usePolling }: PluginOptions): Plugin {
  const modules = libraryMode ? dirs : flattenDirectories(dirs);
  const run = createRunner(modules);

  return {
    name: 'vite-svelte-imports-plugin',
    async buildStart() {
      for (const dir of modules) {
        await createIndexFile(dir);
      }
    },
    async configureServer(server: ViteDevServer) {
      const absoluteDirs = modules.map((dir:string) => path.resolve(dir));

      // use chokidar directly instead of ViteDevServer to be able to set polling
      // since docker volumes might not propagate unlink event correctly
      // vite implementation does not use polling by default
      try {
        const watcher = usePolling
          ? chokidar.watch(absoluteDirs, {
            ignoreInitial: true,
            usePolling,
            ignored: /index\.ts$/
          })
          : server.watcher;

        watcher.on('all', async (event, file) => {
          if (!['add', 'unlink'].includes(event)) {
            return;
          }

          if (event === 'unlink') {
            watcher.unwatch(file);
          }

          await run(file);
        });
      } catch (error) {}
    }
  };
}