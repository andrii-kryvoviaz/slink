import { browser } from '$app/environment';
import { writable } from 'svelte/store';
import type { Writable } from 'svelte/store';

import type {
  Setter,
  Settings,
  SettingsCombinedValue,
  SettingsKey,
} from '@slink/lib/settings/Settings.types';
import { SettingsMap } from '@slink/lib/settings/SettingsMap';
import { Theme } from '@slink/lib/settings/index';
import { HistorySetter } from '@slink/lib/settings/setters/history';
import type { HistorySettings } from '@slink/lib/settings/setters/history';
import { SidebarSetter } from '@slink/lib/settings/setters/sidebar';
import type { SidebarSettings } from '@slink/lib/settings/setters/sidebar';
import { ThemeSetter } from '@slink/lib/settings/setters/theme';
import { UserAdminSetter } from '@slink/lib/settings/setters/userAdmin';
import type { UserAdminSettings } from '@slink/lib/settings/setters/userAdmin';

import { cookie } from '@slink/utils/http/cookie';
import { useWritable } from '@slink/utils/store/contextAwareStore';
import { tryJson } from '@slink/utils/string/json';

type SettingsValueTypes = {
  theme: Theme;
  sidebar: SidebarSettings;
  userAdmin: UserAdminSettings;
  history: HistorySettings;
};

export class SettingsManager {
  public static get instance(): SettingsManager {
    return new SettingsManager();
  }

  private _settings: SettingsMap = new SettingsMap();

  private _setters: {
    [K in SettingsKey]: Setter<K, SettingsValueTypes[K]>;
  } = {
    theme: ThemeSetter,
    sidebar: SidebarSetter,
    userAdmin: UserAdminSetter,
    history: HistorySetter,
  };

  public get<T extends SettingsKey>(
    key: T,
    defaultValue?: unknown | undefined,
  ): SettingsCombinedValue<T> {
    if (!this._settings.has(key)) {
      return this._fallback(key, defaultValue as string);
    }

    return this._formReturnValue(this._settings.get(key) as Settings[T]);
  }

  public set<T extends SettingsKey>(key: T, value: unknown): void {
    this._setValue(key, value);

    let cookieValue: string =
      typeof value === 'string' ? value : JSON.stringify(value);

    cookie.set(`settings.${key}`, cookieValue);
  }

  public getSettingKeys(): SettingsKey[] {
    return this._settings.keys();
  }

  private _formReturnValue<T extends SettingsKey>(
    data: Settings[T],
  ): SettingsCombinedValue<T> {
    const { value, ...rest } = data;

    if (!value) {
      return rest as SettingsCombinedValue<T>;
    }

    return {
      ...rest,
      subscribe: value.subscribe,
    } as unknown as SettingsCombinedValue<T>;
  }

  private _fallback<T extends SettingsKey>(
    key: T,
    defaultValue: string,
  ): SettingsCombinedValue<T> {
    if (browser) {
      const cookieValue = cookie.get(`settings.${key}`, defaultValue);

      this._setValue(key, tryJson(cookieValue ?? ''));
      return this._formReturnValue(this._settings.get(key) as Settings[T]);
    }

    const oneTimeStoreValue = writable(defaultValue);
    return this._formReturnValue(
      this._apply(key, oneTimeStoreValue as Writable<SettingsValueTypes[T]>),
    );
  }

  private _setValue<T extends SettingsKey>(key: T, value: unknown): void {
    const existingSetting = this._settings.get(key);
    let store = existingSetting?.value;

    if (!store) {
      const newStore = useWritable(key, value);
      const initialStore = this._apply(
        key,
        newStore as Writable<SettingsValueTypes[T]>,
      );
      this._settings.set(key, initialStore);
    } else {
      (store as Writable<unknown>).set(value);
    }
  }

  private _apply<K extends SettingsKey>(
    key: K,
    store: Writable<SettingsValueTypes[K]>,
  ): Settings[K] {
    if (!this._setters[key]) {
      return store as unknown as Settings[K];
    }

    return this._setters[key](store) as Settings[K];
  }
}
