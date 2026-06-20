import { adapter as svelte, svelteKitDefaultHeuristic } from '@wuchale/svelte';
import { defaultHeuristic, defineConfig } from 'wuchale';
import { adapter as js } from 'wuchale/adapter-vanilla';

const CLASS_BUILDERS = new Set(['className', 'cn', 'clsx']);

const isRoutePath = (message) => (message.msgStr?.[0] ?? '').startsWith('/');

const jsDefaultHeuristic = (message) => {
  if (!defaultHeuristic(message)) {
    return false;
  }
  return message.details.scope !== 'script' || message.details.funcName != null;
};

export default defineConfig({
  locales: ['en', 'de', 'es', 'fr', 'it', 'pl', 'uk', 'ja', 'zh'],
  adapters: {
    main: svelte({
      loader: 'sveltekit',
      heuristic: (message) => {
        if (message.details?.call === 'Symbol') {
          return false;
        }
        if (message.details?.call && CLASS_BUILDERS.has(message.details.call)) {
          return false;
        }
        if (message.details?.attribute === 'class') {
          return false;
        }
        if (isRoutePath(message)) {
          return false;
        }
        return svelteKitDefaultHeuristic(message);
      },
    }),
    js: js({
      loader: 'vite',
      heuristic: (message) => {
        if (isRoutePath(message)) {
          return false;
        }
        return jsDefaultHeuristic(message);
      },
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
