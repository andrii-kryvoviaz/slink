import { createSvelteHeuristic, adapter as svelte } from '@wuchale/svelte';
import { defaultHeuristic, defaultHeuristicOpts, defineConfig } from 'wuchale';
import { adapter as js } from 'wuchale/adapter-vanilla';

const svelteHeuristic = createSvelteHeuristic({
  ...defaultHeuristicOpts,
  ignoreCalls: [
    ...defaultHeuristicOpts.ignoreCalls,
    'Symbol',
    'className',
    'cn',
    'clsx',
  ],
});

const inClassAttribute = (text) =>
  text.path.some(
    (scope) => scope.type === 'attribute' && scope.name === 'class',
  );

const isRoutePath = (text) => String(text.body).startsWith('/');

export default defineConfig({
  locales: ['en', 'de', 'es', 'fr', 'it', 'pl', 'uk', 'ja', 'zh'],
  adapters: {
    main: svelte({
      loader: 'sveltekit',
      heuristic: (text, file) =>
        !inClassAttribute(text) && svelteHeuristic(text, file),
    }),
    js: js({
      loader: 'vite',
      heuristic: (text, file) =>
        !isRoutePath(text) && defaultHeuristic(text, file),
      files: [
        'src/**/+{page,layout}.{js,ts}',
        'src/**/+{page,layout}.server.{js,ts}',
        'src/lib/utils/i18n/!(*.svelte).ts',
        'src/**/*.language.ts',
      ],
      patterns: [{ name: 'localize', args: ['message', 'other'] }],
    }),
  },
});
