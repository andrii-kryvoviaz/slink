import { sveltekit } from '@sveltejs/kit/vite';
import tailwindcss from '@tailwindcss/vite';
import { readFileSync } from 'fs';
import { join } from 'path';
import { defineConfig } from 'vite';

import iconifyExport from './plugins/vite-iconify-export';
import svelteImports from './plugins/vite-svelte-imports';

const getPackageVersion = (): string => {
  try {
    return JSON.parse(
      readFileSync(join(process.cwd(), 'package.json'), 'utf-8'),
    ).version;
  } catch {
    return 'unknown';
  }
};

export default defineConfig({
  plugins: [
    svelteImports({
      dirs: ['src/api', 'src/feature'],
      enableLogging: true,
    }),
    iconifyExport(),
    sveltekit(),
    tailwindcss(),
  ],
  server: {
    host: '0.0.0.0',
  },
  build: {
    chunkSizeWarningLimit: 1000,
  },
  define: {
    __BUILD_DATE__: JSON.stringify(new Date().toISOString()),
    __COMMIT_HASH__: JSON.stringify(process.env.VITE_COMMIT_HASH || 'unknown'),
    __APP_VERSION__: JSON.stringify(getPackageVersion()),
  },
});
