import { ApiClient } from '@slink/api';

import { invalidate } from '$app/navigation';

import { ValidationException } from '@slink/api/Exceptions';
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
    async (category: SettingCategory, data: SettingCategoryData) => {
      const response = await ApiClient.setting.updateSettings(category, data);
      await invalidate('app:settings');
      return response;
    },
    { debounce: 300, minExecutionTime: 500 },
  );

  private _request = bindRequestState(this._reactiveState);
  private _validationErrors = $state<Record<string, string>>({});

  get error() {
    return this._request.error;
  }

  get errors() {
    return this._validationErrors;
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
    this._validationErrors = {};

    await this._reactiveState.run(category, categoryData);
  };

  handleError = (error: Error) => {
    if (error instanceof ValidationException) {
      this._validationErrors = Object.fromEntries(
        error.violations.map((violation) => [
          violation.property,
          violation.message,
        ]),
      );
      return;
    }

    printErrorsAsToastMessage(error);
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
      if (err) settingsPageInstance!.handleError(err);
    });
  }

  return settingsPageInstance;
}
