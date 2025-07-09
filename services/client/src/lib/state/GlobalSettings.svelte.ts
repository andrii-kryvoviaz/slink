import { ApiClient } from '@slink/api/Client';
import { useState } from '@slink/lib/state/core/ContextAwareState';

import type {
  GlobalSettings,
  SettingCategory,
  SettingCategoryData,
} from '@slink/lib/settings/Type/GlobalSettings';
class GlobalSettingsManager {
  private _settings = $state<GlobalSettings | null>(null);

  public get isInitialized(): boolean {
    return this._settings !== null;
  }

  public get settings(): GlobalSettings {
    return this._settings ?? ({} as GlobalSettings);
  }

  public initialize(initialSettings: GlobalSettings | null): void {
    this._settings = initialSettings;
  }

  constructor() {
    this.refresh();
  }

  public updateCategory(
    category: SettingCategory,
    data: SettingCategoryData,
  ): void {
    if (this._settings) {
      this._settings = {
        ...this._settings,
        [category]: data,
      };
    }
  }

  public async refresh(): Promise<void> {
    try {
      this._settings = await ApiClient.setting.getGlobalSettings();
    } catch {
      this._settings = null;
    }
  }
}

const GLOBAL_SETTINGS = Symbol('GlobalSettings');

let globalSettingsInstance: GlobalSettingsManager | null = null;

export const useGlobalSettings = (): GlobalSettingsManager => {
  if (!globalSettingsInstance) {
    globalSettingsInstance = new GlobalSettingsManager();
  }
  return useState<GlobalSettingsManager>(
    GLOBAL_SETTINGS,
    globalSettingsInstance,
  );
};
