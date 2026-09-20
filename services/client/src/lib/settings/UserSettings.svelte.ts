import { browser } from '$app/environment';
import { MediaQuery } from 'svelte/reactivity';

import { SortOrder } from '@slink/lib/enum/SortOrder';
import {
  Mode,
  type SettingsKey,
  type ViewMode,
  defaultSettings,
  resolveLocale,
  resolveMode,
  resolveTheme,
  settingsKeys,
} from '@slink/lib/settings/Settings.enums';
import {
  resolveSettingsCookies,
  settingsPolicy,
} from '@slink/lib/settings/SettingsPolicy';

import { cookie } from '@slink/utils/http/cookie';
import { type DeepPartial, deepMerge } from '@slink/utils/object/deepMerge';

export type ShareFormat = 'direct' | 'markdown' | 'bbcode' | 'html' | 'image';

export type SidebarState = { expanded: boolean };
export type NavigationState = { expandedGroups: Record<string, boolean> };
export type UserAdminState = { viewMode: ViewMode };
export type TableKeySettings = {
  pageSize: number;
  columnVisibility: Record<string, boolean>;
};
export type TableState = {
  users: TableKeySettings;
  tags: TableKeySettings;
  history: TableKeySettings;
  collections: TableKeySettings;
  shares: TableKeySettings;
};
export type HistoryState = { viewMode: ViewMode };
export type ExploreState = { viewMode: ViewMode };
export type TagsState = { viewMode: ViewMode };
export type CollectionLoadStrategy = 'load_more' | 'infinite_scroll';
export type CollectionsState = {
  viewMode: ViewMode;
  pageSize: number;
  loadStrategy: CollectionLoadStrategy;
};
export type ShareState = { format: ShareFormat };
export type CommentState = { sortOrder: SortOrder };
export type UploadOptionsState = { expanded: boolean };
export type BannersState = { hideExifKeptNotice: boolean };
export type BookmarksState = { viewMode: ViewMode };

export type CookieSettings = { [K in SettingsKey]?: unknown };

export const USER_SETTINGS_BRAND = Symbol.for('slink:user-settings');

function persist(key: SettingsKey, value: unknown): void {
  if (!browser) return;
  cookie.set(
    settingsPolicy.name(key),
    settingsPolicy.encode(value),
    settingsPolicy.options.maxAge,
  );
}

class EnumSetting<T extends string> {
  _value = $state() as T;

  constructor(
    private readonly _key: SettingsKey,
    private readonly _resolve: (value: unknown) => T,
  ) {
    this._value = _resolve(undefined);
  }

  get current(): T {
    return this._value;
  }

  set current(v: T) {
    this._value = v;
    persist(this._key, v);
  }

  hydrate(v: unknown): void {
    this._value = this._resolve(v);
  }
}

class ModeSetting extends EnumSetting<Mode> {
  private readonly _systemDark = new MediaQuery('prefers-color-scheme: dark');

  get isDark(): boolean {
    if (this.current === Mode.SYSTEM) {
      return this._systemDark.current;
    }

    return this.current === Mode.DARK;
  }

  get isLight(): boolean {
    return !this.isDark;
  }
}

class ObjectSetting<T extends object> {
  _value = $state.raw() as T;

  constructor(private readonly _key: SettingsKey) {
    this._value = defaultSettings[_key] as T;
  }

  get current(): T {
    return this._value;
  }

  set current(v: T) {
    this._value = v;
    persist(this._key, v);
  }

  hydrate(v: unknown): void {
    this._value = v as T;
  }
}

export class UserSettings {
  [USER_SETTINGS_BRAND] = true;

  readonly mode = new ModeSetting('mode', resolveMode);
  readonly theme = new EnumSetting('theme', resolveTheme);
  readonly locale = new EnumSetting('locale', resolveLocale);

  readonly _sidebar = new ObjectSetting<SidebarState>('sidebar');
  readonly _navigation = new ObjectSetting<NavigationState>('navigation');
  readonly _userAdmin = new ObjectSetting<UserAdminState>('userAdmin');
  readonly _table = new ObjectSetting<TableState>('table');
  readonly _history = new ObjectSetting<HistoryState>('history');
  readonly _explore = new ObjectSetting<ExploreState>('explore');
  readonly _bookmarks = new ObjectSetting<BookmarksState>('bookmarks');
  readonly _tags = new ObjectSetting<TagsState>('tags');
  readonly _share = new ObjectSetting<ShareState>('share');
  readonly _comment = new ObjectSetting<CommentState>('comment');
  readonly _collections = new ObjectSetting<CollectionsState>('collections');
  readonly _uploadOptions = new ObjectSetting<UploadOptionsState>(
    'uploadOptions',
  );
  readonly _banners = new ObjectSetting<BannersState>('banners');

  private readonly _settings: Record<
    SettingsKey,
    { hydrate(v: unknown): void }
  > = {
    mode: this.mode,
    theme: this.theme,
    locale: this.locale,
    sidebar: this._sidebar,
    navigation: this._navigation,
    userAdmin: this._userAdmin,
    table: this._table,
    history: this._history,
    explore: this._explore,
    bookmarks: this._bookmarks,
    tags: this._tags,
    share: this._share,
    comment: this._comment,
    uploadOptions: this._uploadOptions,
    banners: this._banners,
    collections: this._collections,
  };

  constructor(initial?: CookieSettings) {
    if (initial) {
      this._apply(initial);
    } else if (browser) {
      this._apply(resolveSettingsCookies((name) => cookie.get(name)));
    }
  }

  get sidebar(): SidebarState {
    return this._sidebar.current;
  }

  set sidebar(v: SidebarState) {
    this._sidebar.current = v;
  }

  get navigation(): NavigationState {
    return this._navigation.current;
  }

  set navigation(v: NavigationState) {
    this._navigation.current = v;
  }

  get userAdmin(): UserAdminState {
    return this._userAdmin.current;
  }

  set userAdmin(v: UserAdminState) {
    this._userAdmin.current = v;
  }

  get table(): TableState {
    return this._table.current;
  }

  set table(v: TableState) {
    this._table.current = v;
  }

  updateTable(partial: DeepPartial<TableState>): void {
    this._table.current = deepMerge(this._table.current, partial);
  }

  get history(): HistoryState {
    return this._history.current;
  }

  set history(v: HistoryState) {
    this._history.current = v;
  }

  get explore(): ExploreState {
    return this._explore.current;
  }

  set explore(v: ExploreState) {
    this._explore.current = v;
  }

  get bookmarks(): BookmarksState {
    return this._bookmarks.current;
  }

  set bookmarks(v: BookmarksState) {
    this._bookmarks.current = v;
  }

  get tags(): TagsState {
    return this._tags.current;
  }

  set tags(v: TagsState) {
    this._tags.current = v;
  }

  get share(): ShareState {
    return this._share.current;
  }

  set share(v: ShareState) {
    this._share.current = v;
  }

  get comment(): CommentState {
    return this._comment.current;
  }

  set comment(v: CommentState) {
    this._comment.current = v;
  }

  get collections(): CollectionsState {
    return this._collections.current;
  }

  set collections(v: CollectionsState) {
    this._collections.current = v;
  }

  get uploadOptions(): UploadOptionsState {
    return this._uploadOptions.current;
  }

  set uploadOptions(v: UploadOptionsState) {
    this._uploadOptions.current = v;
  }

  get banners(): BannersState {
    return this._banners.current;
  }

  set banners(v: BannersState) {
    this._banners.current = v;
  }

  private _apply(data: CookieSettings): void {
    for (const key of settingsKeys) {
      const value = data[key];
      if (value == null) continue;

      this._settings[key].hydrate(value);
    }
  }

  reset(): void {
    this._apply(defaultSettings);
  }
}
