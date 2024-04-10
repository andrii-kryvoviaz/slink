import { Theme } from '@slink/lib/settings/Settings.enums';
import type {
  CookieSettings,
  SettingsKey,
} from '@slink/lib/settings/Settings.types';
import { SettingsManager } from '@slink/lib/settings/SettingsManager';
import type { Handle } from '@sveltejs/kit';

export * from './Settings.enums';

export const settings = SettingsManager.instance;

const defaultSettings: { [K in SettingsKey]?: any } = {
  theme: Theme.DARK,
};

export const setCookieSettingsOnLocals: Handle = async ({ event, resolve }) => {
  const settingsKeys = settings.getSettingKeys();

  event.locals.settings = settingsKeys.reduce((acc, key: SettingsKey) => {
    acc[key] =
      event.cookies.get(`settings.${key}`) || defaultSettings[key] || null;
    return acc;
  }, {} as CookieSettings);

  return resolve(event);
};
