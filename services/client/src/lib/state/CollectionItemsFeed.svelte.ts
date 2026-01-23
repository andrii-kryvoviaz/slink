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

export class CollectionItemsFeed extends AbstractPaginatedFeed<CollectionItem> {
  private _collectionId: string | null = $state(null);
  private _collection: CollectionResponse | null = $state(null);

  public constructor() {
    super({
      defaultPageSize: 24,
      useCursor: false,
      appendMode: 'always',
    });
  }

  public get collection(): CollectionResponse | null {
    return this._collection;
  }

  public get images(): ImageListingItem[] {
    return this._items
      .filter((item) => item.image !== undefined)
      .map((item) => item.image!);
  }

  public getImageIndex(imageId: string): number {
    return this.images.findIndex((img) => img.id === imageId);
  }

  public updateImage(
    image: ImageListingItem,
    updates: Partial<ImageListingItem>,
  ): void {
    const item = this._items.find((i) => i.image?.id === image.id);
    if (item && item.image) {
      item.image = { ...item.image, ...updates };
    }
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
      return { data: [], meta: { size: 0, page: 1, total: 0 } };
    }

    const { page = 1 } = params;

    if (!this._collection) {
      this._collection = await ApiClient.collection.getById(this._collectionId);
    }

    const response = await ApiClient.collection.getItems(
      this._collectionId,
      page,
    );

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
    const index = this._items.findIndex((i) => i.itemId === itemId);
    if (index !== -1) {
      this._items.splice(index, 1);
    }
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
