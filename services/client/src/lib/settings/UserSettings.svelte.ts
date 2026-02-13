import { browser } from '$app/environment';

import { SortOrder } from '@slink/lib/enum/SortOrder';
import { Theme } from '@slink/lib/settings/Settings.enums';

import { cookie } from '@slink/utils/http/cookie';
import { deepMerge } from '@slink/utils/object/deepMerge';
import { tryJson } from '@slink/utils/string/json';

export type ViewMode = 'grid' | 'list';
export type ShareFormat = 'direct' | 'markdown' | 'bbcode' | 'html' | 'image';

export type SidebarState = { expanded: boolean };
export type NavigationState = { expandedGroups: Record<string, boolean> };
export type UserAdminState = { viewMode: ViewMode };
export type TableKeySettings = {
  pageSize: number;
  columnVisibility: Record<string, boolean>;
};
export type TableState = { users: TableKeySettings; tags: TableKeySettings };
export type HistoryState = { viewMode: ViewMode };
export type ShareState = { format: ShareFormat };
export type CommentState = { sortOrder: SortOrder };
export type UploadOptionsState = { expanded: boolean };

export type SettingsKey =
  | 'theme'
  | 'sidebar'
  | 'navigation'
  | 'userAdmin'
  | 'table'
  | 'history'
  | 'share'
  | 'comment'
  | 'uploadOptions';

export type CookieSettings = { [K in SettingsKey]?: unknown };

export const settingsKeys: SettingsKey[] = [
  'theme',
  'sidebar',
  'navigation',
  'userAdmin',
  'table',
  'history',
  'share',
  'comment',
  'uploadOptions',
];

export const defaultSettings: Record<SettingsKey, unknown> = {
  theme: Theme.DARK,
  sidebar: { expanded: true },
  navigation: { expandedGroups: {} },
  userAdmin: { viewMode: 'list' },
  table: {
    users: {
      pageSize: 12,
      columnVisibility: {
        displayName: true,
        username: true,
        status: true,
        roles: true,
      },
    },
    tags: {
      pageSize: 10,
      columnVisibility: {
        name: true,
        imageCount: true,
        children: true,
      },
    },
  },
  history: { viewMode: 'list' },
  share: { format: 'direct' },
  comment: { sortOrder: SortOrder.Asc },
  uploadOptions: { expanded: false },
};

export const USER_SETTINGS_BRAND = Symbol.for('slink:user-settings');

function persist(key: string, value: unknown): void {
  if (!browser) return;
  const cookieValue = typeof value === 'string' ? value : JSON.stringify(value);
  cookie.set(`settings.${key}`, cookieValue, 31536000);
}

class ThemeState {
  _value = $state<Theme>(Theme.DARK);

  get current(): Theme {
    return this._value;
  }

  set current(v: Theme) {
    this._value = v;
    persist('theme', v);
  }

  get isDark(): boolean {
    return this._value === Theme.DARK;
  }

  get isLight(): boolean {
    return this._value === Theme.LIGHT;
  }

  hydrate(v: Theme) {
    this._value = v;
  }
}

export class UserSettings {
  [USER_SETTINGS_BRAND] = true;

  readonly theme = new ThemeState();

  _sidebar = $state<SidebarState>(defaultSettings.sidebar as SidebarState);
  _navigation = $state<NavigationState>(
    defaultSettings.navigation as NavigationState,
  );
  _userAdmin = $state<UserAdminState>(
    defaultSettings.userAdmin as UserAdminState,
  );
  _table = $state<TableState>(defaultSettings.table as TableState);
  _history = $state<HistoryState>(defaultSettings.history as HistoryState);
  _share = $state<ShareState>(defaultSettings.share as ShareState);
  _comment = $state<CommentState>(defaultSettings.comment as CommentState);
  _uploadOptions = $state<UploadOptionsState>(
    defaultSettings.uploadOptions as UploadOptionsState,
  );

  constructor(initial?: CookieSettings) {
    if (initial) {
      this._apply(initial as Record<string, unknown>);
    } else if (browser) {
      this._apply(this._readCookies());
    }
  }

  private _readCookies(): Record<string, unknown> {
    return settingsKeys.reduce(
      (acc, key) => {
        const value = cookie.get(`settings.${key}`);
        acc[key] = value ? tryJson(value) : defaultSettings[key];
        return acc;
      },
      {} as Record<string, unknown>,
    );
  }

  get sidebar(): SidebarState {
    return this._sidebar;
  }

  set sidebar(v: SidebarState) {
    this._sidebar = v;
    persist('sidebar', v);
  }

  get navigation(): NavigationState {
    return this._navigation;
  }

  set navigation(v: NavigationState) {
    this._navigation = v;
    persist('navigation', v);
  }

  get userAdmin(): UserAdminState {
    return this._userAdmin;
  }

  set userAdmin(v: UserAdminState) {
    this._userAdmin = v;
    persist('userAdmin', v);
  }

  get table(): TableState {
    return this._table;
  }

  set table(v: TableState) {
    this._table = v;
    persist('table', v);
  }

  updateTable(
    partial: Partial<Record<keyof TableState, Partial<TableKeySettings>>>,
  ): void {
    this._table = deepMerge(
      this._table as Record<string, unknown>,
      partial as Record<string, unknown>,
    ) as TableState;
    persist('table', this._table);
  }

  get history(): HistoryState {
    return this._history;
  }

  set history(v: HistoryState) {
    this._history = v;
    persist('history', v);
  }

  get share(): ShareState {
    return this._share;
  }

  set share(v: ShareState) {
    this._share = v;
    persist('share', v);
  }

  get comment(): CommentState {
    return this._comment;
  }

  set comment(v: CommentState) {
    this._comment = v;
    persist('comment', v);
  }

  get uploadOptions(): UploadOptionsState {
    return this._uploadOptions;
  }

  set uploadOptions(v: UploadOptionsState) {
    this._uploadOptions = v;
    persist('uploadOptions', v);
  }

  private _apply(data: Record<string, unknown>): void {
    for (const [key, value] of Object.entries(data)) {
      if (value == null) continue;

      const field = (this as Record<string, unknown>)[key];
      if (field && typeof field === 'object' && 'hydrate' in field) {
        (field as { hydrate: (v: unknown) => void }).hydrate(value);
      } else {
        (this as Record<string, unknown>)[`_${key}`] = value;
      }
    }
  }

  reset(): void {
    this._apply(defaultSettings as Record<string, unknown>);
  }
}
