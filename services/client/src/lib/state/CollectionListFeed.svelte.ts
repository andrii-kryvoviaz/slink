import { ApiClient } from '@slink/api';

import type { CollectionResponse } from '@slink/api/Response';

import { AbstractPaginatedFeed } from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';
import type {
  LoadParams,
  PaginatedResponse,
  SearchParams,
} from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';
import { useState } from '@slink/lib/state/core/ContextAwareState';
import { debounce } from '@slink/lib/utils/time/debounce';

class CollectionListFeed extends AbstractPaginatedFeed<CollectionResponse> {
  private _searchTerm = $state('');

  private readonly _debouncedSearchLoad = debounce(() => {
    this.load({ searchTerm: this._searchTerm });
  }, 300);

  public constructor() {
    super({
      defaultPageSize: 12,
      useCursor: true,
      appendMode: 'always',
    });
  }

  override get key() {
    return 'collections' as const;
  }

  protected async fetchData(
    params: LoadParams & SearchParams,
  ): Promise<PaginatedResponse<CollectionResponse>> {
    const { cursor, limit = this._config.defaultPageSize, searchTerm } = params;

    return ApiClient.collection.getList(
      limit,
      cursor,
      searchTerm || this._searchTerm,
    );
  }

  get search() {
    return this._searchTerm;
  }

  set search(value: string) {
    if (this._searchTerm === value) return;
    this._searchTerm = value;
    this._debouncedSearchLoad();
  }

  protected _getItemId(item: CollectionResponse): string {
    return item.id;
  }

  public async createCollection(
    name: string,
    description?: string,
  ): Promise<CollectionResponse> {
    const collection = await ApiClient.collection.create({ name, description });
    this.addItem(collection);
    return collection;
  }

  public async updateCollection(
    collectionId: string,
    data: { name?: string; description?: string },
  ): Promise<CollectionResponse> {
    const updated = await ApiClient.collection.update(collectionId, data);
    this.replaceItem(updated);
    return updated;
  }

  public async deleteCollection(
    collectionId: string,
    deleteImages: boolean = false,
  ): Promise<void> {
    await ApiClient.collection.remove(collectionId, deleteImages);
    this.removeItem(collectionId);
  }
}

const COLLECTION_LIST_FEED = Symbol('CollectionListFeed');

const collectionListFeed = new CollectionListFeed();

export const useCollectionListFeed = (): CollectionListFeed => {
  return useState<CollectionListFeed>(COLLECTION_LIST_FEED, collectionListFeed);
};
