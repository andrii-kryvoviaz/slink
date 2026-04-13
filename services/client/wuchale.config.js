import { adapter as svelte } from '@wuchale/svelte';
import { defineConfig } from 'wuchale';
import { adapter as js } from 'wuchale/adapter-vanilla';

export default defineConfig({
  locales: ['en', 'uk', 'de', 'zh'],
  adapters: {
    main: svelte({
      loader: 'sveltekit',
    }),
    js: js({
      loader: 'vite',
      files: [
        'src/**/+{page,layout}.{js,ts}',
        'src/**/+{page,layout}.server.{js,ts}',
        'src/lib/utils/i18n/!(*.svelte).ts',

        'src/routes/**/*.questions.ts',
      ],
      patterns: [{ name: 'localize', args: ['message', 'other'] }],
    }),
  },
});
