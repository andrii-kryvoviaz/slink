import { ApiClient } from '@slink/api';
import type {
  ImageFilter,
  ImageOutputFormat,
  ImageParams,
} from '@slink/feature/Image';
import {
  ShareExpirationState,
  SharePasswordState,
  ShareState,
} from '@slink/feature/Share';

import { printErrorsAsToastMessage } from '$lib/utils/ui/printErrorsAsToastMessage';
import { PreviewUrl } from '$lib/utils/url';

export interface ShareCardImage {
  id: string;
  fileName: string;
  supportsFormatConversion?: boolean;
  isAnimated?: boolean;
}

export interface ShareCardConfig {
  getImage: () => ShareCardImage;
  getFilter: () => ImageFilter;
  getResizeParams: () => Partial<ImageParams>;
  onPublished?: (shareId: string) => void | Promise<void>;
}

const applyFormatToFileName = (
  fileName: string,
  format: ImageOutputFormat,
): string => {
  if (format === 'original') {
    return fileName;
  }

  const lastDotIndex = fileName.lastIndexOf('.');

  if (lastDotIndex === -1) {
    return `${fileName}.${format}`;
  }

  return `${fileName.substring(0, lastDotIndex)}.${format}`;
};

export class ShareCardState {
  private _config: ShareCardConfig;

  private _selectedFormat: ImageOutputFormat = $state('original');
  private _copiedKey: string | null = $state(null);
  private _copiedShareUrl: string | null = $state(null);

  private _loadTimer: ReturnType<typeof setTimeout> | null = null;
  private _activeLoad: Promise<void> | null = null;
  private _loadedKey: string | null = null;

  private _expirationIntent: ShareExpirationState;
  private _passwordIntent: SharePasswordState;
  private _share: ShareState;

  readonly originalFormat: string = $derived.by(() => {
    const fileName = this._config.getImage().fileName;
    const lastDotIndex = fileName.lastIndexOf('.');

    if (lastDotIndex === -1) {
      return '';
    }

    return fileName.substring(lastDotIndex + 1);
  });

  readonly formattedFileName: string = $derived.by(() =>
    applyFormatToFileName(
      this._config.getImage().fileName,
      this._selectedFormat,
    ),
  );

  readonly directLink: string = $derived.by(() => {
    const { width, height, crop } = this._config.getResizeParams();
    const filter = this._config.getFilter();

    return PreviewUrl.image(
      this.formattedFileName,
      {
        width,
        height,
        crop,
        filter: filter === 'none' ? undefined : filter,
      },
      { absolute: true },
    );
  });

  private readonly _paramsKey: string = $derived.by(() =>
    JSON.stringify({
      imageId: this._config.getImage().id,
      filter: this._config.getFilter(),
      format: this._selectedFormat,
      resize: this._config.getResizeParams(),
    }),
  );

  private readonly _compositionKey: string = $derived.by(() =>
    JSON.stringify({
      params: this._paramsKey,
      expiresAt: this._expirationIntent.enabled
        ? (this._expirationIntent.date?.toISOString() ?? null)
        : null,
      password: this._passwordIntent.enabled
        ? this._passwordIntent.pendingPassword
        : null,
    }),
  );

  constructor(config: ShareCardConfig) {
    this._config = config;

    this._expirationIntent = new ShareExpirationState({
      getShareId: () => null,
    });
    this._passwordIntent = new SharePasswordState({
      getShareId: () => null,
      intent: true,
    });

    this._share = new ShareState({
      fetchShare: () => {
        const image = this._config.getImage();
        const filter = this._config.getFilter();
        const resizeParams = this._config.getResizeParams();

        return ApiClient.image.shareImage(image.id, {
          ...resizeParams,
          format: this._selectedFormat,
          filter: filter === 'none' ? undefined : filter,
        });
      },
      attributes: {
        expiration: this._expirationIntent,
        password: this._passwordIntent,
      },
    });

    $effect(() => {
      const key = this._paramsKey;

      this._loadTimer = setTimeout(() => {
        this._loadTimer = null;
        void this._loadShare(key);
      }, 300);

      return () => this._clearLoadTimer();
    });
  }

  get selectedFormat(): ImageOutputFormat {
    return this._selectedFormat;
  }

  get shareUrl(): string | undefined {
    return this._share.shareUrl ?? undefined;
  }

  get isLoading(): boolean {
    return this._share.isLoading;
  }

  get share(): ShareState {
    return this._share;
  }

  setFormat = (format: ImageOutputFormat): void => {
    this._selectedFormat = format;
  };

  ensurePublished = async (): Promise<string | void> => {
    const isPublishedComposition =
      this._copiedKey === this._compositionKey &&
      this._share.shareUrl === this._copiedShareUrl;

    if (isPublishedComposition) {
      return this._share.shareUrl ?? undefined;
    }

    return this._publishComposition();
  };

  private _clearLoadTimer(): void {
    if (this._loadTimer === null) {
      return;
    }

    clearTimeout(this._loadTimer);
    this._loadTimer = null;
  }

  private _loadShare(key: string): Promise<void> {
    this._activeLoad = this._share.load().then(() => {
      this._activeLoad = null;
      this._loadedKey = key;
    });

    return this._activeLoad;
  }

  private async _ensureLoaded(): Promise<void> {
    this._clearLoadTimer();

    if (this._activeLoad) {
      await this._activeLoad;
    }

    if (this._loadedKey === this._paramsKey) {
      return;
    }

    await this._loadShare(this._paramsKey);
  }

  private async _publishComposition(): Promise<string | void> {
    const key = this._compositionKey;

    await this._ensureLoaded();

    const shareId = this._share.shareId;

    if (shareId === null) {
      this._loadedKey = null;
      return;
    }

    try {
      await this._applyIntent(shareId);
      await ApiClient.image.publishShare(shareId);
      await this._config.onPublished?.(shareId);
    } catch (error: unknown) {
      printErrorsAsToastMessage(error as Error);
      return;
    }

    this._copiedKey = key;
    this._copiedShareUrl = this._share.shareUrl;

    return this._share.shareUrl ?? undefined;
  }

  private async _applyIntent(shareId: string): Promise<void> {
    const expiresAt = this._expirationIntent.enabled
      ? this._expirationIntent.date
      : null;

    if (expiresAt !== null) {
      await ApiClient.share.setExpiration(shareId, expiresAt);
    }

    const password = this._passwordIntent.enabled
      ? this._passwordIntent.pendingPassword
      : null;

    if (password !== null) {
      await ApiClient.share.setPassword(shareId, password);
    }
  }
}
