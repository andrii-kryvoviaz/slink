import type { Handle } from '@sveltejs/kit';

import {
  type CookieSettings,
  type SettingsKey,
  UserSettings,
  defaultSettings,
  settingsKeys,
} from '@slink/lib/settings/UserSettings.svelte';

import { tryJson } from '@slink/utils/string/json';

export { Theme } from './Settings.enums';

export const setCookieSettingsOnLocals: Handle = async ({ event, resolve }) => {
  const cookieData = settingsKeys.reduce((acc, key: SettingsKey) => {
    let value =
      event.cookies.get(`settings.${key}`) || defaultSettings[key] || null;
    acc[key] = tryJson(value as string);

    return acc;
  }, {} as CookieSettings);

  event.locals.settings = new UserSettings(cookieData);

  return resolve(event);
};
