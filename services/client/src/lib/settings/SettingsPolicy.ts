import type { CookiePolicy } from '@slink/lib/auth/CookiePolicy';
import {
  type SettingsKey,
  settingsKeys,
} from '@slink/lib/settings/UserSettings.svelte';

export const settingsPolicy: CookiePolicy<SettingsKey> = {
  keys: settingsKeys,
  name: (key) => `settings.${key}`,
  encode: (value) =>
    typeof value === 'string' ? value : JSON.stringify(value),
  options: { maxAge: 31536000, httpOnly: false, sameSite: 'strict' },
};
