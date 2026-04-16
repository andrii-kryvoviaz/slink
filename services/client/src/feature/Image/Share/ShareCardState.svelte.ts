import { ApiClient } from '@slink/api';
import type {
  ImageFilter,
  ImageOutputFormat,
  ImageParams,
} from '@slink/feature/Image';

import { bindRequestState } from '$lib/utils/store/bindRequestState.svelte';
import { printErrorsAsToastMessage } from '$lib/utils/ui/printErrorsAsToastMessage';
import { routes } from '$lib/utils/url/routes';

import { ReactiveState } from '@slink/api/ReactiveState';
import type { ShareResponse } from '@slink/api/Response';

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
  private _shareUrl: string | undefined = $state(undefined);

  private _share = bindRequestState<ShareResponse>(
    ReactiveState<ShareResponse>(
      (
        imageId: string,
        params: {
          width?: number;
          height?: number;
          crop?: boolean;
          format?: string;
          filter?: string;
        },
      ) => ApiClient.image.shareImage(imageId, params),
      { minExecutionTime: 200, debounce: 300 },
    ),
  );

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

    $effect(() => {
      const image = this._config.getImage();
      const filter = this._config.getFilter();
      const resizeParams = this._config.getResizeParams();

      this._share.run(image.id, {
        ...resizeParams,
        format: this._selectedFormat,
        filter: filter === 'none' ? undefined : filter,
      });
    });

    $effect(() => {
      if (this._share.error) {
        printErrorsAsToastMessage(this._share.error);
        this._shareUrl = undefined;
        return;
      }

      const response = this._share.data;

      if (!response) {
        return;
      }

      this._shareUrl = routes.share.fromResponse(response);
    });

    $effect(() => {
      return () => {
        this._share.dispose();
      };
    });
  }

  get selectedFormat(): ImageOutputFormat {
    return this._selectedFormat;
  }

  get shareUrl(): string | undefined {
    return this._shareUrl;
  }

  get isLoading(): boolean {
    return this._share.isLoading;
  }

  handleFormatChange = (format: ImageOutputFormat): void => {
    this._selectedFormat = format;
  };

  handleBeforeCopy = async (): Promise<string | void> => {
    let shareData = this._share.data;

    if (!shareData) {
      const image = this._config.getImage();
      const filter = this._config.getFilter();
      const resizeParams = this._config.getResizeParams();

      await this._share.run(image.id, {
        ...resizeParams,
        format: this._selectedFormat,
        filter: filter === 'none' ? undefined : filter,
      });

      if (this._share.error) {
        printErrorsAsToastMessage(this._share.error);
        return;
      }

      if (!this._share.data) {
        return;
      }

      shareData = this._share.data;
    }

    await ApiClient.image.publishShare(shareData.shareId);

    return routes.share.fromResponse(shareData);
  };
}

export function createShareCardState(config: ShareCardConfig): ShareCardState {
  return new ShareCardState(config);
}
