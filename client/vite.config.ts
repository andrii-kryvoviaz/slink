import { sveltekit } from '@sveltejs/kit/vite';
import { defineConfig } from 'vite';

import iconifyExport from './plugins/vite-iconify-export';
import svelteImports from './plugins/vite-svelte-imports';

export default defineConfig({
  plugins: [
    svelteImports({
      dirs: ['src/components/Feature', 'src/components/UI', 'src/api'],
      enableLogging: true,
    }),
    iconifyExport(),
    sveltekit(),
  ],
  server: {
    host: '0.0.0.0',
  },
});
