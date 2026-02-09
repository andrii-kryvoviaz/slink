import { ApiClient } from '@slink/api';

import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';

import type { CollectionResponse } from '@slink/api/Response';

export class CollectionSelectionState {
  private _selectedIds: string[] = $state([]);
  private _collections: CollectionResponse[] = $state([]);
  private _isLoading: boolean = $state(false);
  private _isLoaded: boolean = $state(false);

  get selectedIds() {
    return this._selectedIds;
  }

  get selectedCollections() {
    return this._collections.filter((c) => this._selectedIds.includes(c.id));
  }

  get collections() {
    return this._collections;
  }

  get isLoading() {
    return this._isLoading;
  }

  get isEmpty() {
    return this._collections.length === 0;
  }

  get isLoaded() {
    return this._isLoaded;
  }

  setSelected(collectionIds: string[]) {
    this._selectedIds = [...collectionIds];
  }

  isSelected(collectionId: string): boolean {
    return this._selectedIds.includes(collectionId);
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

  toggle(collection: CollectionResponse): boolean {
    const isSelected = this.isSelected(collection.id);

    if (isSelected) {
      this._selectedIds = this._selectedIds.filter(
        (id) => id !== collection.id,
      );
    } else {
      this._selectedIds = [...this._selectedIds, collection.id];
    }

    return !isSelected;
  }

  remove(collectionId: string) {
    this._selectedIds = this._selectedIds.filter((id) => id !== collectionId);
  }

  addCollection(collection: CollectionResponse) {
    this._collections = [collection, ...this._collections];
  }

  reset() {
    this._selectedIds = [];
    this._collections = [];
    this._isLoading = false;
    this._isLoaded = false;
  }
}

export function createCollectionSelectionState(): CollectionSelectionState {
  return new CollectionSelectionState();
}
