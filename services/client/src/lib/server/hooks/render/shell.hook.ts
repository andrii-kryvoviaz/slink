import type { Handle } from '@sveltejs/kit';

import { defineHook } from '../define';

const applyShell: Handle = async ({ event, resolve }) => {
  const { mode, theme, locale } = event.locals.settings;

  const values: Record<string, string> = {
    mode: mode.current,
    theme: theme.current,
    locale: locale.current,
  };

  return resolve(event, {
    transformPageChunk: ({ html }) =>
      html.replace(/%app\.(\w+)%/g, (match, key) => values[key] ?? match),
  });
};

export default defineHook({ handle: applyShell });
