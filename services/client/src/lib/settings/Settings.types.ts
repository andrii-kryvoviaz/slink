import type { Readable, Writable } from 'svelte/store';

import { Theme } from '@slink/lib/settings/index';
import type {
  HistorySettings,
  HistoryViewMode,
} from '@slink/lib/settings/setters/history';
import type { SidebarSettings } from '@slink/lib/settings/setters/sidebar';
import type {
  UserAdminSettings,
  ViewMode,
} from '@slink/lib/settings/setters/userAdmin';

export type Settings = {
  theme: SettingsValue<Theme> & {
    isDark: Readable<boolean>;
    isLight: Readable<boolean>;
  };
  sidebar: SettingsValue<SidebarSettings> & {
    expanded: Readable<boolean>;
  };
  userAdmin: SettingsValue<UserAdminSettings> & {
    viewMode: Readable<ViewMode>;
  };
  history: SettingsValue<HistorySettings> & {
    viewMode: Readable<HistoryViewMode>;
  };
};

export type SettingsKey = keyof Settings;

export type SettingsValue<T> = {
  value: Writable<T>;
};

export type Setter<K extends SettingsKey, T> = (
  value: Writable<T>,
) => Settings[K];

export type SettingsCombinedValue<T extends SettingsKey> = Omit<
  Settings[T],
  'value'
> &
  Readable<T>;

export type CookieSettings = { [K in SettingsKey]?: string };
