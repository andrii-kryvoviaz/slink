import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';

import { ApiClient } from '@slink/api/Client';
import type { Tag } from '@slink/api/Resources/TagResource';

export class TagSelectionState {
  private _tags: Tag[] = $state([]);
  private _isLoading: boolean = $state(false);
  private _isLoaded: boolean = $state(false);

  get tags() {
    return this._tags;
  }

  get isLoading() {
    return this._isLoading;
  }

  get isEmpty() {
    return this._tags.length === 0;
  }

  get isLoaded() {
    return this._isLoaded;
  }

  async load() {
    if (this._isLoaded) return;

    this._isLoading = true;
    try {
      const response = await ApiClient.tag.getList({
        limit: 100,
        includeChildren: true,
        orderBy: 'path',
        order: 'asc',
      });
      this._tags = this.flattenTags(response.data);
      this._isLoaded = true;
    } catch {
      toast.error('Failed to load tags');
    } finally {
      this._isLoading = false;
    }
  }

  private flattenTags(tags: Tag[]): Tag[] {
    const seen = new Set<string>();
    const result: Tag[] = [];
    const flatten = (items: Tag[]) => {
      for (const tag of items) {
        if (!seen.has(tag.id)) {
          seen.add(tag.id);
          result.push(tag);
        }
        if (tag.children && tag.children.length > 0) {
          flatten(tag.children);
        }
      }
    };
    flatten(tags);
    return result;
  }

  addTag(tag: Tag) {
    this._tags = [tag, ...this._tags];
  }

  reset() {
    this._tags = [];
    this._isLoading = false;
    this._isLoaded = false;
  }
}

export function createTagSelectionState(): TagSelectionState {
  return new TagSelectionState();
}
