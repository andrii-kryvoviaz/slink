import type {
  ImageListingItem,
  ImageListingResponse,
  ListingMetadata,
} from '@slink/api/Response';
import type { RequestStateOptions } from '@slink/lib/state/core/AbstractHttpState.svelte';

import { ApiClient } from '@slink/api/Client';
import { AbstractHttpState } from '@slink/lib/state/core/AbstractHttpState.svelte';
import { useState } from '@slink/lib/state/core/ContextAwareState';
import { deepMerge } from '@slink/utils/object/deepMerge';

class UploadHistoryFeed extends AbstractHttpState<ImageListingResponse> {
  private _items: ImageListingItem[] = $state([]);
  private _meta: ListingMetadata = $state({} as ListingMetadata);

  public constructor() {
    super();
    this.reset();
  }

  public reset() {
    this._items = [];
    this._meta = {
      page: 1,
      size: 12,
      total: 0,
    };
  }

  public add(item: ImageListingItem) {
    this._items = [item].concat(this._items);
  }

  public replace(item: ImageListingItem) {
    this._items = this._items.map((existing) =>
      existing.id === item.id ? item : existing,
    );
  }

  public update(id: string, data: ImageListingItem) {
    const index = this._items.findIndex((item) => item.id === id);

    if (index !== -1) {
      this._items[index] = deepMerge(this._items[index], data);
    }
  }

  public async load(
    { page, limit }: { page?: number; limit?: number } = {},
    options?: RequestStateOptions,
  ) {
    page ??= this._meta.page;
    limit ??= this._meta.size;

    if (this.isDirty && page === this._meta.page) {
      return;
    }

    await this.fetch(
      () => ApiClient.image.getHistory(page, limit),
      (response) => {
        this._items = this._items.concat(
          response.data.filter((item) => {
            return !this._items.some((existing) => existing.id === item.id);
          }),
        );

        this._meta = response.meta;
      },
      options,
    );
  }

  public async nextPage(options?: RequestStateOptions) {
    if (this._items.length >= this._meta.total) {
      return;
    }

    await this.load(
      { page: this._meta.page + 1, limit: this._meta.size },
      options,
    );
  }

  public removeItem(id: string) {
    this._items = this._items.filter((item) => item.id !== id);

    if (this._items.length === 0) {
      this.reset();
    }

    if (this._items.length > 0) {
      this.load({ page: this._meta.page, limit: this._meta.size });
    }
  }

  get items(): ImageListingItem[] {
    return this._items;
  }

  get meta(): ListingMetadata {
    return this._meta;
  }

  get hasMore(): boolean {
    return this._meta.page < Math.ceil(this._meta.total / this._meta.size);
  }

  get hasItems(): boolean {
    return this._items.length > 0;
  }

  get isEmpty(): boolean {
    return !this.hasItems && this.isDirty;
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
