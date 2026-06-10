import type { Handle } from '@sveltejs/kit';
import { runWithLocale } from 'wuchale/load-utils/server';

import { defineHook } from '../define';

const applyClientLocale: Handle = async ({ event, resolve }) => {
  const locale = event.locals.locale;

  return await runWithLocale(locale, () =>
    resolve(event, {
      transformPageChunk: ({ html }) => html.replace('%app.locale%', locale),
    }),
  );
};

export default defineHook({ handle: applyClientLocale });
