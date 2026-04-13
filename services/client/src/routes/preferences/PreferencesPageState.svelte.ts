import type { UserPreferencesResponse } from '@slink/api/Response';

import { LandingPage } from '@slink/lib/enum/LandingPage';
import { Locale } from '@slink/lib/settings/Settings.enums';

type ChangeHandler = (value: unknown) => void | Promise<void>;

export class PreferencesPageState {
  locale = $state(Locale.EN);
  landingPage = $state(LandingPage.Explore);
  visibility = $state('private');
  license = $state('');
  syncToImages = $state(false);

  private _snapshot: Record<string, unknown> = {};
  private _changeHandlers: Array<{ field: string; handler: ChangeHandler }> =
    [];

  constructor(preferences: UserPreferencesResponse) {
    this.locale = (preferences?.['display.language'] as Locale) ?? Locale.EN;
    this.landingPage =
      preferences?.['navigation.landingPage'] ?? LandingPage.Explore;
    this.visibility = preferences?.['image.defaultVisibility'] ?? 'private';
    this.license = preferences?.['license.default'] ?? '';
    this._takeSnapshot();
  }

  onChanged(field: string, handler: ChangeHandler) {
    this._changeHandlers.push({ field, handler });
  }

  async commit() {
    this.syncToImages = false;

    for (const { field, handler } of this._changeHandlers) {
      const current = (this as any)[field];
      if (current !== this._snapshot[field]) {
        await handler(current);
      }
    }

    this._takeSnapshot();
  }

  private _takeSnapshot() {
    this._snapshot = {
      locale: this.locale,
      landingPage: this.landingPage,
      visibility: this.visibility,
      license: this.license,
    };
  }
}
