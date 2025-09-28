import { ApiClient } from '@slink/api/Client';
import type { Tag } from '@slink/api/Resources/TagResource';
import type { ImageListingItem } from '@slink/api/Response';

import { AbstractPaginatedFeed } from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';
import type {
  LoadParams,
  PaginatedResponse,
  SearchParams,
} from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';
import { useState } from '@slink/lib/state/core/ContextAwareState';

import { deepMerge } from '@slink/utils/object/deepMerge';

interface TagFilter {
  selectedTags: Tag[];
  requireAllTags: boolean;
}

class UploadHistoryFeed extends AbstractPaginatedFeed<ImageListingItem> {
  private _tagFilter: TagFilter = $state({
    selectedTags: [],
    requireAllTags: false,
  });

  public constructor() {
    super({
      defaultPageSize: 12,
      useCursor: false,
      appendMode: 'always',
    });
  }

  protected async fetchData(
    params: LoadParams & SearchParams,
  ): Promise<PaginatedResponse<ImageListingItem>> {
    const { page = 1, limit = 12 } = params;
    const tagIds = this._tagFilter.selectedTags.map((tag) => tag.id);

    return ApiClient.image.getHistory(
      page,
      limit,
      undefined,
      true,
      tagIds.length > 0 ? tagIds : undefined,
      this._tagFilter.requireAllTags,
    );
  }

  protected _getItemId(item: ImageListingItem): string {
    return item.id;
  }

  public update(id: string, data: Partial<ImageListingItem>): void {
    const index = this._items.findIndex((item) => item.id === id);
    if (index !== -1) {
      this._items[index] = deepMerge(
        this._items[index],
        data,
      ) as ImageListingItem;
    }
  }

  public override async load(
    params: LoadParams & SearchParams = {},
    options?: Parameters<typeof this.fetch>[2],
  ): Promise<void> {
    const { page = this._meta.page } = params;

    if (this.isDirty && page === this._meta.page) {
      return;
    }

    await super.load(params, options);
  }

  public setTagFilter(tags: Tag[], requireAllTags: boolean = false): void {
    const hasChanged =
      this._tagFilter.selectedTags.length !== tags.length ||
      this._tagFilter.selectedTags.some(
        (tag, index) => tag.id !== tags[index]?.id,
      ) ||
      this._tagFilter.requireAllTags !== requireAllTags;

    if (hasChanged) {
      this._tagFilter = { selectedTags: [...tags], requireAllTags };
      this.reset();
    }
  }

  public get tagFilter(): TagFilter {
    return { ...this._tagFilter };
  }

  public clearTagFilter(): void {
    if (
      this._tagFilter.selectedTags.length > 0 ||
      this._tagFilter.requireAllTags
    ) {
      this._tagFilter = { selectedTags: [], requireAllTags: false };
      this.reset();
    }
  }
}

const UPLOAD_HISTORY_FEED = Symbol('UploadHistoryFeed');

const uploadHistoryFeed = new UploadHistoryFeed();

export const useUploadHistoryFeed = (
  func: ((state: UploadHistoryFeed) => void) | undefined = undefined,
): UploadHistoryFeed => {
  if (func) {
    func(uploadHistoryFeed);
  }

  return useState<UploadHistoryFeed>(UPLOAD_HISTORY_FEED, uploadHistoryFeed);
};
