import { ApiClient } from '@slink/api';

import { ValidationException } from '@slink/api/Exceptions';

import { debounce } from '@slink/lib/utils/time/debounce';

import { withMinDuration } from '@slink/utils/time/withMinDuration';

import { uploadFailedMessage } from './customizationSettings.language';

export class LogoUpload {
  private _uploading = $state(false);
  private _error = $state('');
  private _previewUrl = $state('');
  private _previewPending = $state(false);
  private _previewError = $state(false);

  constructor(initialPreviewUrl: string) {
    this._previewUrl = initialPreviewUrl;
  }

  get uploading() {
    return this._uploading;
  }

  get error() {
    return this._error;
  }

  get previewUrl() {
    return this._previewUrl;
  }

  get previewError() {
    return this._previewError;
  }

  get showLoader() {
    return this._uploading || this._previewPending;
  }

  schedulePreview(url: string): void {
    this._previewError = false;
    this._applyPreview(url);
  }

  onPreviewLoaded(): void {
    this._previewPending = false;
    this._previewError = false;
  }

  onPreviewError(): void {
    this._previewPending = false;
    this._previewError = true;
  }

  async upload(file: File): Promise<string | null> {
    this._uploading = true;
    this._error = '';

    try {
      const { url } = await withMinDuration(
        ApiClient.setting.uploadCustomizationLogo(file),
        500,
      );

      if (url !== this._previewUrl) {
        this._previewPending = true;
        this._previewUrl = url;
      }

      return url;
    } catch (error) {
      this._error = this._resolveErrorMessage(error);
      return null;
    } finally {
      this._uploading = false;
    }
  }

  private _applyPreview = debounce((url: string) => {
    this._previewUrl = url;
  }, 500);

  private _resolveErrorMessage(error: unknown): string {
    if (error instanceof ValidationException) {
      return error.violations[0]?.message ?? uploadFailedMessage();
    }
    return uploadFailedMessage();
  }
}
