import { sveltekit } from '@sveltejs/kit/vite';
import tailwindcss from '@tailwindcss/vite';
import { defineConfig } from 'vite';

import iconifyExport from './plugins/vite-iconify-export';
import svelteImports from './plugins/vite-svelte-imports';

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
});
