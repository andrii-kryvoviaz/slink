import type { UserPreferencesResponse } from '@slink/api/Response/User/UserPreferencesResponse';

import type { CookiePolicy } from '@slink/lib/auth/CookiePolicy';
import {
  type SettingsKey,
  defaultSettings,
  defaultViewModes,
  isViewModeSettingsKey,
  resolveLocale,
  resolveTheme,
  settingsKeys,
  supportedViewModes,
} from '@slink/lib/settings/Settings.enums';

import { deepMerge, isObject } from '@slink/utils/object/deepMerge';
import { tryJson } from '@slink/utils/string/json';

function withSupportedViewMode(
  key: SettingsKey,
  merged: Record<string, unknown>,
): Record<string, unknown> {
  if (!isViewModeSettingsKey(key)) {
    return merged;
  }

  if (supportedViewModes[key].some((mode) => mode === merged.viewMode)) {
    return merged;
  }

  return { ...merged, viewMode: defaultViewModes[key] };
}

function decodeSetting(key: SettingsKey, raw: string | undefined): unknown {
  const fallback = defaultSettings[key];

  if (raw === undefined || raw === '') {
    return fallback;
  }

  const parsed = tryJson(raw);

  if (isObject(fallback) && isObject(parsed)) {
    return withSupportedViewMode(key, deepMerge(fallback, parsed));
  }

  if (isObject(fallback)) {
    return fallback;
  }

  return parsed;
}

export const settingsPolicy: CookiePolicy<SettingsKey> = {
  keys: settingsKeys,
  name: (key) => `settings.${key}`,
  encode: (value) =>
    typeof value === 'string' ? value : JSON.stringify(value),
  decode: decodeSetting,
  options: { maxAge: 31536000, httpOnly: false, sameSite: 'strict' },
};

export const resolveSettingsCookies = (
  reader: (name: string) => string | undefined,
): Record<SettingsKey, unknown> =>
  settingsKeys.reduce(
    (acc, key) => {
      acc[key] = settingsPolicy.decode(key, reader(settingsPolicy.name(key)));
      return acc;
    },
    {} as Record<SettingsKey, unknown>,
  );

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
