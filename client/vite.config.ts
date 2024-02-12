import { defineConfig } from 'vite';

import { sveltekit } from '@sveltejs/kit/vite';
import svelteImports from './plugins/SvelteImportsPlugin';
import fs from 'fs';

export default defineConfig({
  plugins: [
    svelteImports({ dirs: ['src/components'], enableLogging: true }),
    sveltekit(),
  ],
});
