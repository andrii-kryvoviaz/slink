import { ApiClient } from '@slink/api/Client';
import type { ImageListingItem } from '@slink/api/Response';

import { AbstractPaginatedFeed } from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';
import type {
  LoadParams,
  PaginatedResponse,
  SearchParams,
} from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';
import { useState } from '@slink/lib/state/core/ContextAwareState';

import { deepMerge } from '@slink/utils/object/deepMerge';

class UploadHistoryFeed extends AbstractPaginatedFeed<ImageListingItem> {
  public constructor() {
    super({
      defaultPageSize: 12,
      useCursor: false,
      appendMode: 'always',
    });
  }

  protected async fetchData(
    params: LoadParams & SearchParams,
  ): Promise<PaginatedResponse<ImageListingItem>> {
    const { page = 1, limit = 12 } = params;
    return ApiClient.image.getHistory(page, limit, undefined, true);
  }

  protected _getItemId(item: ImageListingItem): string {
    return item.id;
  }

  public update(id: string, data: Partial<ImageListingItem>): void {
    const index = this._items.findIndex((item) => item.id === id);
    if (index !== -1) {
      this._items[index] = deepMerge(
        this._items[index],
        data,
      ) as ImageListingItem;
    }
  }

  public override async load(
    params: LoadParams & SearchParams = {},
    options?: Parameters<typeof this.fetch>[2],
  ): Promise<void> {
    const { page = this._meta.page } = params;

    if (this.isDirty && page === this._meta.page) {
      return;
    }

    await super.load(params, options);
  }
}

const UPLOAD_HISTORY_FEED = Symbol('UploadHistoryFeed');

const uploadHistoryFeed = new UploadHistoryFeed();

export const useUploadHistoryFeed = (
  func: ((state: UploadHistoryFeed) => void) | undefined = undefined,
): UploadHistoryFeed => {
  if (func) {
    func(uploadHistoryFeed);
  }

  return useState<UploadHistoryFeed>(UPLOAD_HISTORY_FEED, uploadHistoryFeed);
};
