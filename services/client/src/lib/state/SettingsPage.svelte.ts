import { ApiClient } from '@slink/api';

import { ReactiveState } from '@slink/api/ReactiveState';
import type { EmptyResponse } from '@slink/api/Response';

import type {
  SettingCategory,
  SettingCategoryData,
} from '@slink/lib/settings/Type/GlobalSettings';
import { useGlobalSettings } from '@slink/lib/state/GlobalSettings.svelte';

import { printErrorsAsToastMessage } from '@slink/utils/ui/printErrorsAsToastMessage';

class SettingsPageState {
  private _globalSettingsManager = useGlobalSettings();
  private _categoryBeingSaved: SettingCategory | null = $state(null);
  private _isLoading = $state(false);
  private _unsubscribes: (() => void)[] = [];

  private _reactiveState = ReactiveState<EmptyResponse>(
    (category: SettingCategory, data: SettingCategoryData) => {
      return ApiClient.setting.updateSettings(category, data);
    },
    { debounce: 300, minExecutionTime: 500 },
  );

  constructor() {
    this._unsubscribes.push(
      this._reactiveState.isLoading.subscribe((value) => {
        this._isLoading = value;
      }),
    );

    this._unsubscribes.push(
      this._reactiveState.error.subscribe((err) => {
        if (err) {
          printErrorsAsToastMessage(err);
        }
      }),
    );
  }

  get settings() {
    return this._globalSettingsManager.settings;
  }

  get isInitialized() {
    return this._globalSettingsManager.isInitialized;
  }

  get categoryBeingSaved() {
    return this._categoryBeingSaved;
  }

  get isLoading() {
    return this._isLoading;
  }

  isLoadingCategory = (category: SettingCategory) => {
    return this._isLoading && this._categoryBeingSaved === category;
  };

  handleSave = async ({ category }: { category: SettingCategory }) => {
    const { [category]: categoryData } = this._globalSettingsManager.settings;
    this._categoryBeingSaved = category;

    await this._reactiveState.run(category, categoryData);
  };
}

let settingsPageInstance: SettingsPageState | null = null;

export function useSettingsPage(): SettingsPageState {
  if (!settingsPageInstance) {
    settingsPageInstance = new SettingsPageState();
  }
  return settingsPageInstance;
}
