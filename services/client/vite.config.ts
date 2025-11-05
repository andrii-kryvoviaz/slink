import { sveltekit } from '@sveltejs/kit/vite';
import tailwindcss from '@tailwindcss/vite';
import { SvelteKitPWA } from '@vite-pwa/sveltekit';
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
    SvelteKitPWA({
      srcDir: './src',
      mode: 'production',
      strategies: 'generateSW',
      scope: '/',
      base: '/',
      selfDestroying: process.env.SELF_DESTROYING_SW === 'true',
      manifest: false,
      workbox: {
        globPatterns: ['client/**/*.{js,css,ico,png,svg,webp,woff,woff2}'],
        navigateFallback: null,
        runtimeCaching: [
          {
            urlPattern: /^https:\/\/fonts\.googleapis\.com\/.*/i,
            handler: 'CacheFirst',
            options: {
              cacheName: 'google-fonts-cache',
              expiration: {
                maxEntries: 10,
                maxAgeSeconds: 60 * 60 * 24 * 365,
              },
              cacheableResponse: {
                statuses: [0, 200],
              },
            },
          },
          {
            urlPattern: /\/api\/.*/i,
            handler: 'NetworkFirst',
            options: {
              cacheName: 'api-cache',
              networkTimeoutSeconds: 10,
              expiration: {
                maxEntries: 50,
                maxAgeSeconds: 60 * 5,
              },
            },
          },
        ],
      },
      devOptions: {
        enabled: true,
        type: 'module',
        navigateFallback: '/',
      },
    }),
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
