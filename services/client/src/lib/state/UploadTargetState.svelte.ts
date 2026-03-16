import { ApiClient } from '@slink/api';

import { replaceState } from '$app/navigation';

import type { CollectionResponse } from '@slink/api/Response';

import { routes } from '@slink/utils/url';

export class UploadTargetState {
  private _collectionId: string | null;
  private _collection: CollectionResponse | null = $state(null);

  constructor(url: URL) {
    this._collectionId = url.searchParams.get('collection');
    if (this._collectionId) {
      replaceState('/upload', {});
      this._loadCollection(this._collectionId);
    }
  }

  get collection(): CollectionResponse | null {
    return this._collection;
  }

  get hasTargetCollection(): boolean {
    return this._collectionId !== null;
  }

  get redirectUrl(): string | null {
    if (!this._collectionId) return null;
    return routes.collection.detail(this._collectionId);
  }

  private async _loadCollection(id: string) {
    this._collection = await ApiClient.collection.getById(id);
  }
}

export function createUploadTargetState(url: URL): UploadTargetState {
  return new UploadTargetState(url);
}
