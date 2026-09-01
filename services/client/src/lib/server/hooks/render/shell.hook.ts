import type { Handle } from '@sveltejs/kit';

import { resolveMode, resolveTheme } from '@slink/lib/settings/Settings.enums';

import { defineHook } from '../define';

const applyShell: Handle = async ({ event, resolve }) => {
  const mode = resolveMode(event.cookies.get('settings.mode'));
  const theme = resolveTheme(event.cookies.get('settings.theme'));

  const values: Record<string, string> = {
    mode,
    theme,
    locale: event.locals.locale,
  };

  return resolve(event, {
    transformPageChunk: ({ html }) =>
      html.replace(/%app\.(\w+)%/g, (match, key) => values[key] ?? match),
  });
};

export default defineHook({ handle: applyShell });
