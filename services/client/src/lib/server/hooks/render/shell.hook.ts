import type { Handle } from '@sveltejs/kit/hooks';

import { ThemeCatalog } from '@slink/lib/settings/ThemeCatalog';

import { defineHook } from '../define';

const applyShell: Handle = async ({ event, resolve }) => {
  const { mode, theme, locale } = event.locals.settings;
  const themes = new ThemeCatalog(event.locals.globalSettings?.customization);

  const values: Record<string, string> = {
    mode: mode.current,
    theme: themes.selector(themes.resolve(theme.current)),
    locale: locale.current,
  };

  return resolve(event, {
    transformPageChunk: ({ html }) =>
      html.replace(/%app\.(\w+)%/g, (match, key) => values[key] ?? match),
  });
};

export default defineHook({ handle: applyShell });
