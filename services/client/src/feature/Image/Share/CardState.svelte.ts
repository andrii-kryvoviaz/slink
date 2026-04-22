import { ApiClient } from '@slink/api';
import type {
  ImageFilter,
  ImageOutputFormat,
  ImageParams,
} from '@slink/feature/Image';
import { ShareState, type ShareStateRegistry } from '@slink/feature/Share';

import { routes } from '$lib/utils/url/routes';

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
  registry?: ShareStateRegistry | null;
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

  private _share: ShareState;

  private _lastLoadKey: string | null = null;

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

  readonly directLink: string = $derived.by(() =>
    routes.image.view(this.formattedFileName, {}, { absolute: true }),
  );

  constructor(config: ShareCardConfig) {
    this._config = config;

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
      onEnsurePublished: async (shareId) => {
        await ApiClient.image.publishShare(shareId);
        await this._config.onPublished?.(shareId);
      },
      registry: this._config.registry,
    });

    $effect(() => {
      const key = JSON.stringify({
        imageId: this._config.getImage().id,
        filter: this._config.getFilter(),
        format: this._selectedFormat,
        resize: this._config.getResizeParams(),
      });

      if (key === this._lastLoadKey) {
        return;
      }

      this._lastLoadKey = key;
      void this._share.load();
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

  get expiration() {
    return this._share.expiration;
  }

  get share(): ShareState {
    return this._share;
  }

  setFormat = (format: ImageOutputFormat): void => {
    this._selectedFormat = format;
  };

  ensurePublished = async (): Promise<string | void> => {
    return this._share.ensurePublished();
  };
}
