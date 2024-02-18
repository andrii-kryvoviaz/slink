import { defineConfig } from 'vite';

import { sveltekit } from '@sveltejs/kit/vite';
import svelteImports from './plugins/SvelteImportsPlugin';
import iconifyExport from './plugins/vite-iconify-export';

export default defineConfig({
  plugins: [
    svelteImports({ dirs: ['src/components'], enableLogging: true }),
    iconifyExport(),
    sveltekit(),
  ],
  server: {
    host: '0.0.0.0',
  }
});
