import type { ShareListItemResponse } from '@slink/api/Response/Share/ShareListItemResponse';

import { buildQueryString } from '@slink/utils/url/routing';

import { imageRoutes } from '../routes/image';

export type PreviewParams = {
  width?: number;
  height?: number;
  crop?: boolean;
  quality?: number;
  format?: string;
};

export class PreviewUrl {
  static image(fileName: string, params: PreviewParams): string {
    return this._resize(imageRoutes.view(fileName), params);
  }

  static shareable(
    share: ShareListItemResponse,
    params: PreviewParams,
  ): string | null {
    const { previewUrl } = share.shareable;

    if (share.type !== 'image' || !previewUrl) {
      return previewUrl;
    }

    return this._resize(previewUrl, params);
  }

  private static _resize(url: string, params: PreviewParams): string {
    const { format, ...query } = params;

    return `${this._withFormat(url, format)}${buildQueryString(query)}`;
  }

  private static _withFormat(url: string, format?: string): string {
    if (!format) {
      return url;
    }

    const slashIndex = url.lastIndexOf('/');
    const dotIndex = url.lastIndexOf('.');
    const base = dotIndex > slashIndex ? url.slice(0, dotIndex) : url;

    return `${base}.${format}`;
  }
}
