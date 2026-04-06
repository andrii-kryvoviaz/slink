import { ApiClient } from '@slink/api';

import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';

import type { Tag } from '@slink/api/Resources/TagResource';
import type { CollectionResponse } from '@slink/api/Response';

interface ImagePickerDeps<TItem extends { id: string }> {
  load: () => Promise<TItem[]>;
  assign: (imageId: string, itemId: string) => Promise<void>;
  unassign: (imageId: string, itemId: string) => Promise<void>;
  onError: (action: 'load' | 'assign' | 'unassign') => void;
}

export class ImagePickerState<TItem extends { id: string }> {
  private _imageId: string = $state('');
  private _assignedIds: string[] = $state([]);
  private _items: TItem[] = $state([]);
  private _isLoading: boolean = $state(false);
  private _actionLoadingId: string | null = $state(null);
  private _isLoaded: boolean = $state(false);

  private _deps: ImagePickerDeps<TItem>;

  constructor(deps: ImagePickerDeps<TItem>) {
    this._deps = deps;
  }

  get items() {
    return this._items;
  }

  get isLoading() {
    return this._isLoading;
  }

  get actionLoadingId() {
    return this._actionLoadingId;
  }

  get isEmpty() {
    return this._items.length === 0;
  }

  get isLoaded() {
    return this._isLoaded;
  }

  setImage(imageId: string, assignedIds: string[] = []) {
    if (this._imageId !== imageId) {
      this._imageId = imageId;
      this._assignedIds = [...assignedIds];
      this._isLoaded = false;
    }
  }

  isAssigned(itemId: string): boolean {
    return this._assignedIds.includes(itemId);
  }

  isToggling(itemId: string): boolean {
    return this._actionLoadingId === itemId;
  }

  async load() {
    if (this._isLoaded) return;

    this._isLoading = true;
    try {
      this._items = await this._deps.load();
      this._isLoaded = true;
    } catch {
      this._deps.onError('load');
    } finally {
      this._isLoading = false;
    }
  }

  async toggle(
    item: TItem,
  ): Promise<{ added: boolean; itemId: string } | null> {
    if (this._actionLoadingId || !this._imageId) return null;

    this._actionLoadingId = item.id;
    const isAssigned = this.isAssigned(item.id);

    try {
      if (isAssigned) {
        await this._deps.unassign(this._imageId, item.id);
        this._assignedIds = this._assignedIds.filter((id) => id !== item.id);
        return { added: false, itemId: item.id };
      } else {
        await this._deps.assign(this._imageId, item.id);
        this._assignedIds = [...this._assignedIds, item.id];
        return { added: true, itemId: item.id };
      }
    } catch {
      this._deps.onError(isAssigned ? 'unassign' : 'assign');
      return null;
    } finally {
      this._actionLoadingId = null;
    }
  }

  addItem(item: TItem) {
    this._items = [item, ...this._items];
  }

  reset() {
    this._imageId = '';
    this._assignedIds = [];
    this._items = [];
    this._isLoading = false;
    this._actionLoadingId = null;
    this._isLoaded = false;
  }
}

export function createCollectionPickerState(): ImagePickerState<CollectionResponse> {
  return new ImagePickerState<CollectionResponse>({
    load: () => ApiClient.collection.getList(50).then((r) => r.data),
    assign: (imageId, itemId) => ApiClient.collection.addItem(itemId, imageId),
    unassign: (imageId, itemId) =>
      ApiClient.collection.removeItem(itemId, imageId),
    onError: (action) =>
      toast.error(
        {
          load: 'Failed to load collections',
          assign: 'Failed to add to collection',
          unassign: 'Failed to remove from collection',
        }[action],
      ),
  });
}

export function createImageTagPickerState(): ImagePickerState<Tag> {
  return new ImagePickerState<Tag>({
    load: () =>
      ApiClient.tag
        .getList({
          limit: 50,
          orderBy: 'path',
          order: 'asc',
          includeChildren: true,
        })
        .then((r) => r.data),
    assign: (imageId, itemId) => ApiClient.tag.tagImage(imageId, itemId),
    unassign: (imageId, itemId) => ApiClient.tag.untagImage(imageId, itemId),
    onError: (action) =>
      toast.error(
        {
          load: 'Failed to load tags',
          assign: 'Failed to add tag',
          unassign: 'Failed to remove tag',
        }[action],
      ),
  });
}

export type CollectionImagePickerState = ImagePickerState<CollectionResponse>;
export type TagImagePickerState = ImagePickerState<Tag>;
