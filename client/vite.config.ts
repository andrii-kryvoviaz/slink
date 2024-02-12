import { defineConfig } from 'vite';

import { sveltekit } from '@sveltejs/kit/vite';
import svelteImports from './plugins/SvelteImportsPlugin';
import fs from 'fs';

const componentsDir = 'src/components';
const dirs = fs.readdirSync(componentsDir, { withFileTypes: true })
  .filter((dir) => dir.isDirectory())
  .map((dir) => `${componentsDir}/${dir.name}/`);

export default defineConfig({
  plugins: [
    svelteImports({ dirs }),
    sveltekit(),
  ],
});
