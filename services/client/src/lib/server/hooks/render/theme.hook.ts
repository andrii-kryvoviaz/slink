import type { Handle } from '@sveltejs/kit';

import { resolveTheme } from '@slink/lib/settings/Settings.enums';

import { defineHook } from '../define';

const applyClientTheme: Handle = async ({ event, resolve }) => {
  const theme = resolveTheme(event.cookies.get('settings.theme'));

  return resolve(event, {
    transformPageChunk: ({ html }) => html.replace('%app.theme%', theme),
  });
};

export default defineHook({ handle: applyClientTheme });
