import { sveltekit } from '@sveltejs/kit/vite';
import { defineConfig } from 'vitest/config';

import { svelteKitConfig } from './vite.config.ts';

export default defineConfig({
  plugins: [sveltekit(svelteKitConfig)],
  test: {
    include: ['src/**/*.test.ts'],
    environment: 'node',
  },
});
