import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';

import { ApiClient } from '@slink/api/Client';
import type { CollectionResponse } from '@slink/api/Response';

export class CollectionPickerState {
  private _imageId: string = $state('');
  private _imageCollectionIds: string[] = $state([]);
  private _collections: CollectionResponse[] = $state([]);
  private _isLoading: boolean = $state(false);
  private _actionLoadingId: string | null = $state(null);
  private _isLoaded: boolean = $state(false);

  get collections() {
    return this._collections;
  }

  get isLoading() {
    return this._isLoading;
  }

  get actionLoadingId() {
    return this._actionLoadingId;
  }

  get isEmpty() {
    return this._collections.length === 0;
  }

  get isLoaded() {
    return this._isLoaded;
  }

  setImage(imageId: string, collectionIds: string[] = []) {
    if (this._imageId !== imageId) {
      this._imageId = imageId;
      this._imageCollectionIds = [...collectionIds];
      this._isLoaded = false;
    }
  }

  isInCollection(collectionId: string): boolean {
    return this._imageCollectionIds.includes(collectionId);
  }

  isToggling(collectionId: string): boolean {
    return this._actionLoadingId === collectionId;
  }

  async load() {
    if (this._isLoaded) return;

    this._isLoading = true;
    try {
      const response = await ApiClient.collection.getList(1);
      this._collections = response.data;
      this._isLoaded = true;
    } catch {
      toast.error('Failed to load collections');
    } finally {
      this._isLoading = false;
    }
  }

  async toggle(
    collection: CollectionResponse,
  ): Promise<{ added: boolean; collectionId: string } | null> {
    if (this._actionLoadingId || !this._imageId) return null;

    this._actionLoadingId = collection.id;
    const isInCollection = this.isInCollection(collection.id);

    try {
      if (isInCollection) {
        await ApiClient.collection.removeItem(collection.id, this._imageId);
        this._imageCollectionIds = this._imageCollectionIds.filter(
          (id) => id !== collection.id,
        );
        return { added: false, collectionId: collection.id };
      } else {
        await ApiClient.collection.addItem(collection.id, this._imageId);
        this._imageCollectionIds = [...this._imageCollectionIds, collection.id];
        return { added: true, collectionId: collection.id };
      }
    } catch {
      toast.error(
        isInCollection
          ? 'Failed to remove from collection'
          : 'Failed to add to collection',
      );
      return null;
    } finally {
      this._actionLoadingId = null;
    }
  }

  addCollection(collection: CollectionResponse) {
    this._collections = [collection, ...this._collections];
  }

  reset() {
    this._imageId = '';
    this._imageCollectionIds = [];
    this._collections = [];
    this._isLoading = false;
    this._actionLoadingId = null;
    this._isLoaded = false;
  }
}

export function createCollectionPickerState(): CollectionPickerState {
  return new CollectionPickerState();
}
