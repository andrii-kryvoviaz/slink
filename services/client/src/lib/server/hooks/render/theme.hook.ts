import type { Handle } from '@sveltejs/kit';

import { Theme } from '@slink/lib/settings/Settings.enums';

import { defineHook } from '../define';

const applyClientTheme: Handle = async ({ event, resolve }) => {
  const theme = event.cookies.get('settings.theme') || Theme.DARK;

  return resolve(event, {
    transformPageChunk: ({ html }) => html.replace('%app.theme%', theme),
  });
};

export default defineHook({ handle: applyClientTheme });
