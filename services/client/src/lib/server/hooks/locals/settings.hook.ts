import {
  type SettingsKey,
  settingsKeys,
} from '@slink/lib/settings/Settings.enums';
import { settingsPolicy } from '@slink/lib/settings/SettingsPolicy';
import {
  type CookieSettings,
  UserSettings,
  defaultSettings,
} from '@slink/lib/settings/UserSettings.svelte';

import { tryJson } from '@slink/utils/string/json';

import { defineHook } from '../define';

export default defineHook({
  init: (event) => {
    const cookieData = settingsKeys.reduce((acc, key: SettingsKey) => {
      let value =
        event.cookies.get(settingsPolicy.name(key)) ||
        defaultSettings[key] ||
        null;
      acc[key] = tryJson(value as string);

      return acc;
    }, {} as CookieSettings);

    event.locals.settings = new UserSettings(cookieData);
  },
});
