import { Theme } from '@slink/lib/settings/index';
import type { SidebarSettings } from '@slink/lib/settings/setters/sidebar';

import type { Readable, Writable } from 'svelte/store';

export type Settings = {
  theme: SettingsValue<Theme> & {
    isDark: Readable<boolean>;
    isLight: Readable<boolean>;
  };
  sidebar: SettingsValue<SidebarSettings> & {
    expanded: Readable<boolean>;
  };
};

export type SettingsKey = keyof Settings;

export type SettingsValue<T> = {
  value: Writable<T>;
};

export type Setter<K extends SettingsKey, T> = (
  value: Writable<T>
) => Settings[K];

export type SettingsCombinedValue<T extends SettingsKey> = Omit<
  Settings[T],
  'value'
> &
  Readable<T>;

export type CookieSettings = { [K in SettingsKey]?: string };
