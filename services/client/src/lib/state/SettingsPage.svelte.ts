import { ApiClient } from '@slink/api';

import { ReactiveState } from '@slink/api/ReactiveState';
import type { EmptyResponse } from '@slink/api/Response';

import type {
  GlobalSettings,
  SettingCategory,
  SettingCategoryData,
} from '@slink/lib/settings/Type/GlobalSettings';
import { bindRequestState } from '@slink/lib/utils/store/bindRequestState.svelte';

import { printErrorsAsToastMessage } from '@slink/utils/ui/printErrorsAsToastMessage';

class SettingsPageState {
  private _settings = $state<GlobalSettings | null>(null);
  private _categoryBeingSaved: SettingCategory | null = $state(null);
  private _reactiveState = ReactiveState<EmptyResponse>(
    (category: SettingCategory, data: SettingCategoryData) => {
      return ApiClient.setting.updateSettings(category, data);
    },
    { debounce: 300, minExecutionTime: 500 },
  );

  private _request = bindRequestState(this._reactiveState);

  get error() {
    return this._request.error;
  }

  initialize(settings: GlobalSettings): void {
    if (!this._settings) {
      this._settings = settings;
    }
  }

  get settings(): GlobalSettings {
    return this._settings ?? ({} as GlobalSettings);
  }

  get isInitialized(): boolean {
    return this._settings !== null;
  }

  get categoryBeingSaved() {
    return this._categoryBeingSaved;
  }

  get isLoading() {
    return this._request.isLoading;
  }

  isLoadingCategory = (category: SettingCategory) => {
    return this._request.isLoading && this._categoryBeingSaved === category;
  };

  handleSave = async ({ category }: { category: SettingCategory }) => {
    const { [category]: categoryData } = this.settings;
    this._categoryBeingSaved = category;

    await this._reactiveState.run(category, categoryData);
  };
}

let settingsPageInstance: SettingsPageState | null = null;

export function useSettingsPage(
  initialSettings?: GlobalSettings,
): SettingsPageState {
  if (!settingsPageInstance) {
    settingsPageInstance = new SettingsPageState();
  }
  if (initialSettings) {
    settingsPageInstance.initialize(initialSettings);
  }

  if (initialSettings) {
    $effect(() => {
      const err = settingsPageInstance!.error;
      if (err) printErrorsAsToastMessage(err);
    });
  }

  return settingsPageInstance;
}
