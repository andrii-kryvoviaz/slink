import type { Handle } from '@sveltejs/kit';

import { Mode } from '@slink/lib/settings/Settings.enums';

import { defineHook } from '../define';

const applyClientMode: Handle = async ({ event, resolve }) => {
  const mode = event.cookies.get('settings.mode') || Mode.DARK;

  return resolve(event, {
    transformPageChunk: ({ html }) => html.replace('%app.mode%', mode),
  });
};

export default defineHook({ handle: applyClientMode });
