import { Dirent } from 'fs';
import fs from 'fs';
import path from 'path';

interface FileInfo {
  path: string;
  name: string;
  extension?: string;
}

export function* walkSync(dir: string): Generator<FileInfo> {
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