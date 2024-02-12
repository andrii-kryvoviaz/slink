import type { Plugin } from 'vite';
import fs, { Dirent } from 'fs';
import path from 'path';

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

export default function SvelteImportsPlugin(options: { dirs: string[] }): Plugin {
  return {
    name: 'vite-svelte-imports-plugin',
    async buildStart() {
      for (const dir of options.dirs) {
        await createIndexFile(dir);
      }
    },
    async handleHotUpdate({ file }) {
      let dir = path.dirname(file);

      while (dir !== path.dirname(dir)) {
        if (fs.existsSync(path.join(dir, 'index.ts'))) {
          break;
        }
        dir = path.dirname(dir);
      }

      if (options.dirs.includes(dir)) {
        await createIndexFile(dir);
      }
    },
  };
}