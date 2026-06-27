import { ApiClient } from '@slink/api';

import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';

import type { Tag } from '@slink/api/Resources/TagResource';
import type { CollectionResponse } from '@slink/api/Response';

import {
  PickerCatalog,
  createCollection,
  createTag,
} from '@slink/lib/state/PickerCatalog.svelte';
import { messages } from '@slink/lib/utils/i18n/messages/toast.language';

interface ImagePickerDeps<TItem extends { id: string }> {
  load: () => Promise<TItem[]>;
  create: (name: string) => Promise<TItem>;
  assign: (imageId: string, itemId: string) => Promise<void>;
  unassign: (imageId: string, itemId: string) => Promise<void>;
  onError: (action: 'load' | 'assign' | 'unassign') => void;
}

export class ImagePickerState<
  TItem extends { id: string },
> extends PickerCatalog<TItem> {
  private _imageId: string = $state('');
  private _assignedIds: string[] = $state([]);
  private _actionLoadingId: string | null = $state(null);

  private _assign: (imageId: string, itemId: string) => Promise<void>;
  private _unassign: (imageId: string, itemId: string) => Promise<void>;
  private _onError: (action: 'load' | 'assign' | 'unassign') => void;

  constructor(deps: ImagePickerDeps<TItem>) {
    super({
      fetch: deps.load,
      create: deps.create,
      onLoadError: () => deps.onError('load'),
    });
    this._assign = deps.assign;
    this._unassign = deps.unassign;
    this._onError = deps.onError;
  }

  get actionLoadingId() {
    return this._actionLoadingId;
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

  async toggle(
    item: TItem,
  ): Promise<{ added: boolean; itemId: string } | null> {
    if (this._actionLoadingId || !this._imageId) return null;

    this._actionLoadingId = item.id;
    const isAssigned = this.isAssigned(item.id);

    try {
      if (isAssigned) {
        await this._unassign(this._imageId, item.id);
        this._assignedIds = this._assignedIds.filter((id) => id !== item.id);
        return { added: false, itemId: item.id };
      } else {
        await this._assign(this._imageId, item.id);
        this._assignedIds = [...this._assignedIds, item.id];
        return { added: true, itemId: item.id };
      }
    } catch {
      this._onError(isAssigned ? 'unassign' : 'assign');
      return null;
    } finally {
      this._actionLoadingId = null;
    }
  }

  reset() {
    super.reset();
    this._imageId = '';
    this._assignedIds = [];
    this._actionLoadingId = null;
  }
}

export function createCollectionPickerState(): ImagePickerState<CollectionResponse> {
  return new ImagePickerState<CollectionResponse>({
    load: () => ApiClient.collection.getList(50).then((r) => r.data),
    create: (name) => createCollection({ name }),
    assign: (imageId, itemId) => ApiClient.collection.addItem(itemId, imageId),
    unassign: (imageId, itemId) =>
      ApiClient.collection.removeItem(itemId, imageId),
    onError: (action) =>
      toast.error(
        {
          load: messages.collection.failedToLoad,
          assign: messages.collection.failedToUpdate,
          unassign: messages.collection.failedToUpdate,
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
    create: (name) => createTag(name),
    assign: (imageId, itemId) => ApiClient.tag.tagImage(imageId, itemId),
    unassign: (imageId, itemId) => ApiClient.tag.untagImage(imageId, itemId),
    onError: (action) =>
      toast.error(
        {
          load: messages.tag.failedToLoad,
          assign: messages.tag.failedToCreate,
          unassign: messages.tag.failedToCreate,
        }[action],
      ),
  });
}

export type CollectionImagePickerState = ImagePickerState<CollectionResponse>;
export type TagImagePickerState = ImagePickerState<Tag>;
