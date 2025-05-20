import chokidar from 'chokidar';
import fs from 'fs';
import path from 'path';

import { walkSync } from '../dependencies/walkDirectory';

export function flattenDirectories(dirs: string[]): string[] {
  return dirs.reduce((acc: string[], dir: string) => {
    const subDirs = fs
      .readdirSync(dir, { withFileTypes: true })
      .filter((dirent) => dirent.isDirectory())
      .map((dirent) => `${dir}/${dirent.name}`);
    return acc.concat(subDirs);
  }, []);
}

export function findIndexDir(file: string): string | undefined {
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

export function createWatcher(dirs: string[], usePolling: boolean) {
  return chokidar.watch(dirs, {
    ignoreInitial: true,
    usePolling,
    ignored: /index\.ts$/,
  });
}

export async function createIndexFile(dir: string): Promise<string> {
  const files = Array.from(walkSync(dir));

  const indexContent: string =
    files
      .map((file) => {
        let relativePath: string = path.relative(
          dir,
          path.join(file.path, file.name),
        );

        if (relativePath.startsWith('/')) {
          relativePath = relativePath.slice(1);
        }

        return file.extension === '.svelte'
          ? `export { default as ${file.name} } from './${relativePath}${file.extension}';`
          : `export * from './${relativePath}';`;
      })
      .join('\n') + '\n';

  const output = path.join(dir, 'index.ts');
  fs.writeFileSync(output, indexContent);

  return output;
}
