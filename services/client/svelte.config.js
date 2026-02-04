import adapter from '@sveltejs/adapter-node';
import { vitePreprocess } from '@sveltejs/vite-plugin-svelte';

/** @type {import('@sveltejs/kit').Config} */
const config = {
  preprocess: vitePreprocess(),

  compilerOptions: {
    warningFilter: (warning) => {
      if (warning.code === 'state_referenced_locally') return false;
      return true;
    },
  },

  kit: {
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
  },
};

export default config;
