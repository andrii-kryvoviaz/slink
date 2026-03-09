import { ApiClient } from '@slink/api';

import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';

import type { Tag } from '@slink/api/Resources/TagResource';

export class TagPickerState {
  private _tags: Tag[] = $state([]);
  private _isLoading: boolean = $state(false);
  private _isLoaded: boolean = $state(false);

  get tags() {
    return this._tags;
  }

  get isLoading() {
    return this._isLoading;
  }

  get isLoaded() {
    return this._isLoaded;
  }

  addTag(tag: Tag) {
    this._tags = [tag, ...this._tags];
  }

  async load() {
    if (this._isLoaded) return;

    this._isLoading = true;
    try {
      const response = await ApiClient.tag.getList({
        limit: 50,
        orderBy: 'path',
        order: 'asc',
        includeChildren: true,
      });
      this._tags = response.data;
      this._isLoaded = true;
    } catch {
      toast.error('Failed to load tags');
    } finally {
      this._isLoading = false;
    }
  }
}

export function createTagPickerState(): TagPickerState {
  return new TagPickerState();
}
