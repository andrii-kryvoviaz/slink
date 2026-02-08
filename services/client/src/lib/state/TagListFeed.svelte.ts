import { ApiClient } from '@slink/api/Client';
import type {
  CreateTagRequest,
  Tag,
  TagListRequest,
  TagListingResponse,
  UpdateTagRequest,
} from '@slink/api/Resources/TagResource';

import { AbstractPaginatedFeed } from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';
import type {
  LoadParams,
  PaginatedResponse,
  SearchParams,
} from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';
import { useState } from '@slink/lib/state/core/ContextAwareState';
import { extractErrorMessage } from '@slink/lib/utils/error/extractErrorMessage';

import { toast } from '@slink/utils/ui/toast-sonner.svelte';

class TagListFeed extends AbstractPaginatedFeed<Tag> {
  private _includeChildren = $state(true);
  private _searchTerm = $state('');
  private _orderBy = $state<'name' | 'path' | 'createdAt' | 'updatedAt'>(
    'updatedAt',
  );
  private _sortOrder = $state<'asc' | 'desc'>('desc');

  public constructor() {
    super({
      defaultPageSize: 10,
      useCursor: false,
      appendMode: 'never',
    });
  }

  protected async fetchData(
    params: LoadParams & SearchParams,
  ): Promise<PaginatedResponse<Tag>> {
    const { page = 1, limit = 20, searchTerm } = params;

    const apiParams: TagListRequest = {
      limit,
      page,
      includeChildren: this._includeChildren,
      searchTerm: searchTerm || this._searchTerm,
      orderBy: this._orderBy,
      order: this._sortOrder,
    };

    const response: TagListingResponse = await ApiClient.tag.getList(apiParams);

    return {
      data: response.data,
      meta: {
        page: response.meta.page,
        size: response.meta.size,
        total: response.meta.total,
        nextCursor: response.meta.nextCursor,
        prevCursor: response.meta.prevCursor,
      },
    };
  }

  protected _getItemId(item: Tag): string {
    return item.id;
  }

  get data() {
    return this._items;
  }

  get loading() {
    return this.isLoading;
  }

  get meta() {
    return this._meta;
  }

  get search() {
    return this._searchTerm;
  }

  set search(value: string) {
    this._searchTerm = value;
    this.load({ searchTerm: value, page: 1 });
  }

  get includeChildren() {
    return this._includeChildren;
  }

  set includeChildren(value: boolean) {
    this._includeChildren = value;
    this.load({ page: 1 });
  }

  get orderBy() {
    return this._orderBy;
  }

  set orderBy(value: 'name' | 'path' | 'createdAt' | 'updatedAt') {
    this._orderBy = value;
    this.load({ page: 1 });
  }

  get order() {
    return this._sortOrder;
  }

  set order(value: 'asc' | 'desc') {
    this._sortOrder = value;
    this.load({ page: 1 });
  }

  setSorting(
    orderBy: 'name' | 'path' | 'createdAt' | 'updatedAt',
    order: 'asc' | 'desc',
  ) {
    this._orderBy = orderBy;
    this._sortOrder = order;
    this.load({ page: 1 });
  }

  async fetchTags(params: TagListRequest = {}) {
    const { searchTerm, ...otherParams } = params;
    await this.load({
      searchTerm: searchTerm || this._searchTerm,
      page: 1,
      limit: this._meta.size || 20,
      ...otherParams,
    });
  }

  public async loadPage(
    page: number,
    shouldAppend: boolean = false,
  ): Promise<void> {
    const currentMode = this._config.appendMode;

    if (!shouldAppend) {
      this._config.appendMode = 'never';
    }

    try {
      await this.load({ page, limit: this._meta.size });
    } finally {
      this._config.appendMode = currentMode;
    }
  }

  async createTag(data: CreateTagRequest): Promise<string> {
    try {
      const response = await ApiClient.tag.create(data);
      toast.success(`Tag "${data.name}" created successfully`);
      await this.refetch();
      return response.id;
    } catch (error: any) {
      const message = extractErrorMessage(
        error,
        'Failed to create tag. Please try again.',
      );
      toast.error(message);
      throw error;
    }
  }

  async deleteTag(id: string): Promise<void> {
    try {
      const tag = this._items.find((item) => item.id === id);
      const tagName = tag?.name || 'tag';

      await ApiClient.tag.deleteTag(id);
      toast.success(`Tag "${tagName}" deleted successfully`);
      await this.refetch();
    } catch (error: any) {
      const message = extractErrorMessage(
        error,
        'Failed to delete tag. Please try again.',
      );
      toast.error(message);
      throw error;
    }
  }

  async moveTag(id: string, parentId?: string | null): Promise<void> {
    try {
      const tag = this._items.find((item) => item.id === id);
      const tagName = tag?.name || 'tag';

      const payload: UpdateTagRequest = {
        parentId: parentId ?? null,
      };

      await ApiClient.tag.updateTag(id, payload);
      toast.success(`Tag "${tagName}" moved successfully`);
      await this.refetch();
    } catch (error: any) {
      const message = extractErrorMessage(
        error,
        'Failed to move tag. Please try again.',
      );
      toast.error(message);
      throw error;
    }
  }

  public removeTag(id: string): void {
    this._items = this._items.filter((item) => item.id !== id);
  }

  async refetch(): Promise<void> {
    await this.load({ page: this._meta.page || 1 });
  }

  clear(): void {
    this._items = [];
    this._searchTerm = '';
    this._meta = {
      page: 1,
      size: this._config.defaultPageSize,
      total: 0,
    };
  }
}

const TAG_LIST_FEED = Symbol('TagListFeed');

const tagListFeed = new TagListFeed();

export const useTagListFeed = (
  func: ((state: TagListFeed) => void) | undefined = undefined,
): TagListFeed => {
  if (func) {
    func(tagListFeed);
  }

  return useState<TagListFeed>(TAG_LIST_FEED, tagListFeed);
};
