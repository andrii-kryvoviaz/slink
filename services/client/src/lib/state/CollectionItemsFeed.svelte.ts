import { ApiClient } from '@slink/api/Client';
import type {
  CollectionItem,
  CollectionResponse,
  ImageListingItem,
} from '@slink/api/Response';
import type { ShareResponse } from '@slink/api/Response/Share/ShareResponse';

import { AbstractPaginatedFeed } from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';
import type {
  LoadParams,
  PaginatedResponse,
  SearchParams,
} from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';
import { useState } from '@slink/lib/state/core/ContextAwareState';

export type MediaItem = ImageListingItem;

export class CollectionItemsFeed extends AbstractPaginatedFeed<CollectionItem> {
  private _collectionId: string | null = $state(null);
  private _collection: CollectionResponse | null = $state(null);

  public constructor() {
    super({
      defaultPageSize: 12,
      useCursor: true,
      appendMode: 'always',
    });
  }

  public get collection(): CollectionResponse | null {
    return this._collection;
  }

  public get media(): MediaItem[] {
    return this._items
      .filter((item) => item.item !== undefined)
      .map((item) => item.item!);
  }

  public getItemIndex(mediaId: string): number {
    return this.media.findIndex((m) => m.id === mediaId);
  }

  public updateItemMedia(
    mediaId: string,
    updates: Partial<MediaItem>,
  ): boolean {
    for (const item of this.items) {
      if (item.item?.id === mediaId) {
        this.update(item.itemId, { item: { ...item.item, ...updates } });
        return true;
      }
    }
    return false;
  }

  public setCollectionId(collectionId: string): void {
    if (this._collectionId !== collectionId) {
      this._collectionId = collectionId;
      this._collection = null;
      this.reset();
    }
  }

  protected async fetchData(
    params: LoadParams & SearchParams,
  ): Promise<PaginatedResponse<CollectionItem>> {
    if (!this._collectionId) {
      return { data: [], meta: { size: 0, total: 0 } };
    }

    const { cursor } = params;

    if (!this._collection) {
      this._collection = await ApiClient.collection.getById(this._collectionId);
    }

    const response = await ApiClient.collection.getItems(
      this._collectionId,
      cursor,
    );

    if (this._collection) {
      this._collection = {
        ...this._collection,
        itemCount: response.meta.total,
      };
    }

    return {
      ...response,
      data: response.data.map((item) => ({ ...item, id: item.itemId })),
    };
  }

  protected _getItemId(item: CollectionItem): string {
    return item.itemId;
  }

  public async addItemToCollection(itemId: string): Promise<void> {
    if (!this._collectionId) return;

    await ApiClient.collection.addItem(this._collectionId, itemId);
    this.reset();
    await this.load({ page: 1 });
  }

  public async removeItemFromCollection(itemId: string): Promise<void> {
    if (!this._collectionId) return;

    await ApiClient.collection.removeItem(this._collectionId, itemId);
    this.reset();
    await this.load({ page: 1 });
  }

  public async reorderItems(orderedItemIds: string[]): Promise<void> {
    if (!this._collectionId) return;

    await ApiClient.collection.reorderItems(this._collectionId, orderedItemIds);
  }

  public async share(): Promise<ShareResponse | null> {
    if (!this._collectionId) return null;

    return ApiClient.collection.share(this._collectionId);
  }

  public async updateDetails(data: {
    name?: string;
    description?: string;
  }): Promise<void> {
    if (!this._collectionId || !this._collection) return;

    const updated = await ApiClient.collection.update(this._collectionId, data);
    this._collection = { ...this._collection, ...updated };
  }
}

const COLLECTION_ITEMS_FEED = Symbol('CollectionItemsFeed');

const collectionItemsFeed = new CollectionItemsFeed();

export const useCollectionItemsFeed = (): CollectionItemsFeed => {
  return useState<CollectionItemsFeed>(
    COLLECTION_ITEMS_FEED,
    collectionItemsFeed,
  );
};
