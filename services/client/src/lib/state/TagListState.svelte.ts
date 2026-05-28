import { ApiClient } from '@slink/api';

import { browser } from '$app/environment';

import type { Tag, TagListingResponse } from '@slink/api/Resources/TagResource';

import { AbstractHttpState } from '@slink/lib/state/core/AbstractHttpState.svelte';

const TAG_SEARCH_LIMIT = 50;

export class TagListState extends AbstractHttpState<TagListingResponse> {
  private _tags: Tag[] = $state([]);

  private constructor() {
    super();
  }

  static create(): TagListState | null {
    if (!browser) return null;
    return new TagListState();
  }

  async load(searchTerm?: string): Promise<void> {
    await this.fetch(
      () =>
        ApiClient.tag.getList({
          ...(searchTerm ? { searchTerm } : {}),
          limit: TAG_SEARCH_LIMIT,
          orderBy: 'path',
          order: 'asc',
          includeChildren: true,
        }),
      (response) => {
        this._tags = response.data;
      },
      { debounce: 300 },
    );
  }

  get tags(): Tag[] {
    return this._tags;
  }
}
