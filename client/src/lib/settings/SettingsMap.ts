import type { Settings, SettingsKey } from '@slink/lib/settings/Settings.types';

export class SettingsMap {
  private _data: { [K in SettingsKey]: Settings[K] | null } = {
    theme: null,
    sidebar: null,
  };

  public has<T extends SettingsKey>(key: T): boolean {
    return !!this._data[key];
  }

  public get<T extends SettingsKey>(key: T): Settings[T] | null {
    return this._data[key] || null;
  }

  public set<T extends SettingsKey>(key: T, value: Settings[T]): void {
    this._data[key] = value;
  }

  public keys(): SettingsKey[] {
    return Object.keys(this._data) as SettingsKey[];
  }
}
