import adapter from '@sveltejs/adapter-node';
import { type Config, sveltekit } from '@sveltejs/kit/vite';
import { vitePreprocess } from '@sveltejs/vite-plugin-svelte';
import tailwindcss from '@tailwindcss/vite';
import { readFileSync } from 'fs';
import { join } from 'path';
import { fileURLToPath } from 'url';
import { defineConfig } from 'vite';
import { wuchale } from 'wuchale/vite';

import hookManifest from './plugins/vite-hook-manifest';
import iconifyExport from './plugins/vite-iconify-export';

const getPackageVersion = (): string => {
  try {
    return JSON.parse(
      readFileSync(join(process.cwd(), 'package.json'), 'utf-8'),
    ).version;
  } catch {
    return 'unknown';
  }
};

export const svelteKitConfig: Config = {
  preprocess: vitePreprocess(),
  compilerOptions: {
    warningFilter: (warning) => {
      if (warning.code === 'state_referenced_locally') return false;
      return true;
    },
  },
  adapter: adapter({
    out: 'build',
  }),
  csrf: {
    trustedOrigins: [process.env.ORIGIN ?? 'http://localhost:3000'],
  },
  alias: {
    '@slink/api': './src/api',
    '@slink/utils': './src/lib/utils',
    '@slink/components': './src/components',
    '@slink/store': './src/lib/utils/store',
    '@slink/ui': './src/ui',
    '@slink': './src',
    '@slink/*': './src/*',
  },
};

export default defineConfig({
  resolve: {
    alias: {
      'crawler-user-agents': fileURLToPath(
        new URL(
          './node_modules/crawler-user-agents/crawler-user-agents.json',
          import.meta.url,
        ),
      ),
    },
  },
  plugins: [
    hookManifest({
      dir: 'src/lib/server/hooks',
      enableLogging: true,
    }),
    iconifyExport(),
    wuchale(),
    sveltekit(svelteKitConfig),
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
