import adapter from '@sveltejs/adapter-node';
import { vitePreprocess } from '@sveltejs/vite-plugin-svelte';

/** @type {import('@sveltejs/kit').Config} */
const config = {
  // Consult https://kit.svelte.dev/docs/integrations#preprocessors
  // for more information about preprocessors
  preprocess: vitePreprocess(),

  kit: {
    // adapter-auto only supports some environments, see https://kit.svelte.dev/docs/adapter-auto for a list.
    // If your environment is not supported or you settled on a specific environment, switch out the adapter.
    // See https://kit.svelte.dev/docs/adapters for more information about adapters.
    adapter: adapter({
      out: 'build',
    }),
    csrf: {
      checkOrigin: false,
    },
    alias: {
      '@slink/api': './src/api',
      '@slink/utils': './src/lib/utils',
      '@slink/components': './src/components',
      '@slink/store': './src/lib/store',
      '@slink': './src',
    },
  },
};

export default config;
