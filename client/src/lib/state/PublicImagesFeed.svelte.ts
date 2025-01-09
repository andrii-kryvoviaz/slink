import type {
  ImageListingItem,
  ImageListingResponse,
  ListingMetadata,
} from '@slink/api/Response';
import type { RequestStateOptions } from '@slink/lib/state/core/AbstractHttpState.svelte';

import { ApiClient } from '@slink/api/Client';
import { AbstractHttpState } from '@slink/lib/state/core/AbstractHttpState.svelte';
import { useState } from '@slink/lib/state/core/ContextAwareState';

class PublicImagesFeed extends AbstractHttpState<ImageListingResponse> {
  private _items: ImageListingItem[] = $state([]);
  private _meta: ListingMetadata = $state({} as ListingMetadata);

  public constructor() {
    super();
    this.reset();
  }

  public reset() {
    this.markDirty(false);
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
      () => ApiClient.image.getPublicImages(page, limit),
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

const PUBLIC_IMAGES_FEED = Symbol('PublicImagesFeed');

const publicImagesFeed = new PublicImagesFeed();

export const usePublicImagesFeed = (): PublicImagesFeed => {
  return useState<PublicImagesFeed>(PUBLIC_IMAGES_FEED, publicImagesFeed);
};
