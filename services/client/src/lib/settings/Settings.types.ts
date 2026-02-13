import type { Readable, Writable } from 'svelte/store';

import { SortOrder } from '@slink/lib/enum/SortOrder';
import { Theme } from '@slink/lib/settings/index';
import type { CommentSettings } from '@slink/lib/settings/setters/comment';
import type {
  HistorySettings,
  HistoryViewMode,
} from '@slink/lib/settings/setters/history';
import type { NavigationSettings } from '@slink/lib/settings/setters/navigation';
import type {
  ShareFormat,
  ShareSettings,
} from '@slink/lib/settings/setters/share';
import type { SidebarSettings } from '@slink/lib/settings/setters/sidebar';
import type { TableSettings } from '@slink/lib/settings/setters/table';
import type { UploadOptionsSettings } from '@slink/lib/settings/setters/uploadOptions';
import type { UserAdminSettings } from '@slink/lib/settings/setters/userAdmin';
import type { ViewMode } from '@slink/lib/settings/setters/viewMode';

export type Settings = {
  theme: SettingsValue<Theme> & {
    isDark: Readable<boolean>;
    isLight: Readable<boolean>;
  };
  sidebar: SettingsValue<SidebarSettings> & {
    expanded: Readable<boolean>;
  };
  navigation: SettingsValue<NavigationSettings> & {
    expandedGroups: Readable<Record<string, boolean>>;
  };
  userAdmin: SettingsValue<UserAdminSettings> & {
    viewMode: Readable<ViewMode>;
  };
  table: SettingsValue<TableSettings> & {
    users: {
      pageSize: Readable<number>;
      columnVisibility: Readable<Record<string, boolean>>;
    };
    tags: {
      pageSize: Readable<number>;
      columnVisibility: Readable<Record<string, boolean>>;
    };
  };
  history: SettingsValue<HistorySettings> & {
    viewMode: Readable<HistoryViewMode>;
  };
  share: SettingsValue<ShareSettings> & {
    format: Readable<ShareFormat>;
  };
  comment: SettingsValue<CommentSettings> & {
    sortOrder: Readable<SortOrder>;
  };
  uploadOptions: SettingsValue<UploadOptionsSettings> & {
    expanded: Readable<boolean>;
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
