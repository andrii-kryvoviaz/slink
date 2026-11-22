import type { UserPreferencesResponse } from '@slink/api/Response';

import { LandingPage } from '@slink/lib/enum/LandingPage';
import type { LicenseId } from '@slink/lib/enum/License';
import { Locale } from '@slink/lib/settings/Settings.enums';
import type { ThemeCatalog } from '@slink/lib/settings/ThemeCatalog';

type ChangeHandler = (value: unknown) => void | Promise<void>;

export class PreferencesPageState {
  locale = $state(Locale.EN);
  theme = $state('');
  private _savedTheme = $state('');
  themeChanged = $derived(this.theme !== this._savedTheme);
  landingPage = $state(LandingPage.Explore);
  visibility = $state('private');
  exifPreference = $state('default');
  externalUploadAutoPublish = $state(false);
  license = $state<LicenseId>('all-rights-reserved');
  syncToImages = $state(false);

  private _snapshot: Record<string, unknown> = {};
  private _changeHandlers: Array<{ field: string; handler: ChangeHandler }> =
    [];

  constructor(
    preferences: UserPreferencesResponse | null,
    themes: ThemeCatalog,
  ) {
    this.locale = (preferences?.['display.language'] as Locale) ?? Locale.EN;
    this.theme = themes.resolve(preferences?.['display.theme']);
    this.landingPage =
      preferences?.['navigation.landingPage'] ?? LandingPage.Explore;
    this.visibility = preferences?.['image.defaultVisibility'] ?? 'private';
    this.exifPreference =
      preferences?.['image.stripExifMetadataOverride'] ?? 'default';
    this.externalUploadAutoPublish =
      preferences?.['image.externalUploadAutoPublish'] ?? false;
    this.license =
      (preferences?.['license.default'] as LicenseId) ?? 'all-rights-reserved';
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
    this._savedTheme = this.theme;
    this._snapshot = {
      locale: this.locale,
      landingPage: this.landingPage,
      visibility: this.visibility,
      exifPreference: this.exifPreference,
      externalUploadAutoPublish: this.externalUploadAutoPublish,
      license: this.license,
    };
  }
}
