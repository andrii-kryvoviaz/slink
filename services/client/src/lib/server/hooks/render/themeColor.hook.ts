import type { Handle } from '@sveltejs/kit';

import { Mode, Theme, resolveTheme } from '@slink/lib/settings/Settings.enums';

import { defineHook } from '../define';

const backgroundStart: Record<Theme, Record<Mode, string>> = {
  [Theme.DEFAULT]: {
    [Mode.LIGHT]: '#ffffff',
    [Mode.DARK]: '#0f172b',
  },
  [Theme.NORD]: {
    [Mode.LIGHT]: '#eceff4',
    [Mode.DARK]: '#1f232b',
  },
};

const resolveMode = (value: string | undefined): Mode => {
  if (!value || value === Mode.DARK) {
    return Mode.DARK;
  }

  return Mode.LIGHT;
};

const applyThemeColor: Handle = async ({ event, resolve }) => {
  const mode = resolveMode(event.cookies.get('settings.mode'));
  const theme = resolveTheme(event.cookies.get('settings.theme'));

  return resolve(event, {
    transformPageChunk: ({ html }) =>
      html.replace('%app.themeColor%', backgroundStart[theme][mode]),
  });
};

export default defineHook({ handle: applyThemeColor });
