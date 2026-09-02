import type { UserPreferencesResponse } from '@slink/api/Response/User/UserPreferencesResponse';

import type { CookiePolicy } from '@slink/lib/auth/CookiePolicy';
import {
  type SettingsKey,
  resolveLocale,
  resolveTheme,
  settingsKeys,
} from '@slink/lib/settings/Settings.enums';

export const settingsPolicy: CookiePolicy<SettingsKey> = {
  keys: settingsKeys,
  name: (key) => `settings.${key}`,
  encode: (value) =>
    typeof value === 'string' ? value : JSON.stringify(value),
  options: { maxAge: 31536000, httpOnly: false, sameSite: 'strict' },
};

type AccountSetting = {
  preference: keyof UserPreferencesResponse;
  resolve: (value: unknown) => string;
};

export const accountSettings = {
  theme: { preference: 'display.theme', resolve: resolveTheme },
  locale: { preference: 'display.language', resolve: resolveLocale },
} as const satisfies Partial<Record<SettingsKey, AccountSetting>>;

export type AccountSettingsKey = keyof typeof accountSettings;

export const accountSettingsPolicy: CookiePolicy<AccountSettingsKey> = {
  ...settingsPolicy,
  keys: Object.keys(accountSettings) as AccountSettingsKey[],
};
