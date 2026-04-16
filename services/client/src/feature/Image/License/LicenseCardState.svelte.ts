import { ApiClient } from '@slink/api';

import { bindRequestState } from '$lib/utils/store/bindRequestState.svelte';
import { printErrorsAsToastMessage } from '$lib/utils/ui/printErrorsAsToastMessage';

import { ReactiveState } from '@slink/api/ReactiveState';

import type { License } from '@slink/lib/enum/License';

export interface LicenseCardImage {
  id: string;
  license?: string | null;
}

export interface LicenseCardConfig {
  getImage: () => LicenseCardImage;
  getLicenses: () => License[];
  getLicensingEnabled: () => boolean;
  onLicenseSaved?: (license: string) => void;
}

export class LicenseCardState {
  private _config: LicenseCardConfig;
  private _prevImageId: string;

  private _selectedLicense: string = $state('');

  private _license = bindRequestState(
    ReactiveState((imageId: string, license: string) =>
      ApiClient.image.updateDetails(imageId, { license }),
    ),
  );

  readonly licenses: License[] = $derived.by(() => this._config.getLicenses());

  readonly licensingEnabled: boolean = $derived.by(() =>
    this._config.getLicensingEnabled(),
  );

  readonly licenseOptions = $derived.by(() =>
    this.licenses.map((license) => ({
      value: license.id,
      label: license.title,
    })),
  );

  constructor(config: LicenseCardConfig) {
    this._config = config;
    const image = config.getImage();
    this._prevImageId = image.id;
    this._selectedLicense = image.license ?? '';

    $effect(() => {
      const current = this._config.getImage();

      if (current.id !== this._prevImageId) {
        this._prevImageId = current.id;
        this._selectedLicense = current.license ?? '';
      }
    });

    $effect(() => {
      const current = this._config.getImage();

      if (
        this._selectedLicense &&
        this._selectedLicense !== (current.license ?? '')
      ) {
        this._save(this._selectedLicense);
      }
    });

    $effect(() => {
      return () => {
        this._license.dispose();
      };
    });
  }

  get selectedLicense(): string {
    return this._selectedLicense;
  }

  set selectedLicense(value: string) {
    this._selectedLicense = value;
  }

  get isLoading(): boolean {
    return this._license.isLoading;
  }

  private _save = async (license: string): Promise<void> => {
    const image = this._config.getImage();
    await this._license.run(image.id, license);

    if (this._license.error) {
      printErrorsAsToastMessage(this._license.error);
      return;
    }

    this._config.onLicenseSaved?.(license);
  };
}

export function createLicenseCardState(
  config: LicenseCardConfig,
): LicenseCardState {
  return new LicenseCardState(config);
}
