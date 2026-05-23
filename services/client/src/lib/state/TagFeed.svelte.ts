import { ApiClient } from '@slink/api';

import { SvelteMap, SvelteSet } from 'svelte/reactivity';

import type {
  CreateTagRequest,
  Tag,
  TagListingResponse,
  TagOrderBy,
} from '@slink/api/Resources/TagResource';

import type { ViewMode } from '@slink/lib/settings';
import { AbstractPaginatedFeed } from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';
import type {
  LoadParams,
  PaginatedResponse,
  SearchParams,
} from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';
import { useState } from '@slink/lib/state/core/ContextAwareState';
import { extractErrorMessage } from '@slink/lib/utils/error/extractErrorMessage';
import { messages } from '@slink/lib/utils/i18n/messages/toast.language';
import { debounce } from '@slink/lib/utils/time/debounce';

import { toast } from '@slink/utils/ui/toast-sonner.svelte';

import { tagStrategyFor } from './TagFetchStrategy';
import type { TagFetchStrategy } from './TagFetchStrategy';

class TagFeed extends AbstractPaginatedFeed<Tag> {
  private _strategy: TagFetchStrategy = tagStrategyFor('table');
  private _searchTerm = $state('');
  private _orderBy = $state<TagOrderBy>('updatedAt');
  private _sortOrder = $state<'asc' | 'desc'>('desc');

  private _expanded = new SvelteSet<string>();
  private _childrenCache = new SvelteMap<string, Tag[]>();
  private _loadingNodes = new SvelteSet<string>();

  private readonly _debouncedSearchLoad = debounce(() => {
    this.load({ searchTerm: this._searchTerm, page: 1 });
  }, 300);

  public constructor() {
    super({
      defaultPageSize: 10,
      useCursor: false,
      appendMode: 'never',
    });
  }

  override get key() {
    return 'tags' as const;
  }

  setViewMode(viewMode: ViewMode): void {
    const strategy = tagStrategyFor(viewMode);
    if (this._strategy === strategy) return;
    this._strategy = strategy;
    this._clearExpansion();
    this.load({ page: 1 });
  }

  override hydrate(hint: { hasItems: boolean; viewMode?: ViewMode }): void {
    if (hint.viewMode) {
      this._strategy = tagStrategyFor(hint.viewMode);
    }
    super.hydrate({ hasItems: hint.hasItems });
  }

  protected async fetchData(
    params: LoadParams & SearchParams,
  ): Promise<PaginatedResponse<Tag>> {
    const response = await this._strategy.fetch({
      page: params.page ?? 1,
      limit: params.limit ?? this._meta.size,
      searchTerm: params.searchTerm ?? this._searchTerm,
      orderBy: this._orderBy,
      order: this._sortOrder,
    });

    return { data: response.data, meta: this._toMeta(response) };
  }

  private _toMeta(response: TagListingResponse) {
    return {
      page: response.meta.page,
      size: response.meta.size,
      total: response.meta.total,
      nextCursor: response.meta.nextCursor,
      prevCursor: response.meta.prevCursor,
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
    if (this._searchTerm === value) return;
    this._searchTerm = value;
    this._clearExpansion();
    this._debouncedSearchLoad();
  }

  get searching(): boolean {
    return this._searchTerm.trim().length > 0;
  }

  get orderBy() {
    return this._orderBy;
  }

  get order() {
    return this._sortOrder;
  }

  setSorting(orderBy: TagOrderBy, order: 'asc' | 'desc') {
    this._orderBy = orderBy;
    this._sortOrder = order;
    this.load({ page: 1 });
  }

  isExpanded(id: string): boolean {
    return this._expanded.has(id);
  }

  getChildren(id: string): Tag[] {
    return this._childrenCache.get(id) ?? [];
  }

  isNodeLoading(id: string): boolean {
    return this._loadingNodes.has(id);
  }

  async toggle(node: Tag): Promise<void> {
    const id = node.id;

    if (this._expanded.has(id)) {
      this._expanded.delete(id);
      return;
    }

    if (!this._childrenCache.has(id)) {
      if (this._loadingNodes.has(id)) {
        return;
      }

      this._loadingNodes.add(id);

      try {
        const response = await ApiClient.tag.getList({
          parentId: id,
          includeChildren: false,
        });
        this._childrenCache.set(id, response.data);
      } catch (error: any) {
        const message = extractErrorMessage(
          error,
          'Failed to load child tags. Please try again.',
        );
        toast.error(message);
        this._loadingNodes.delete(id);
        return;
      }

      this._loadingNodes.delete(id);
    }

    this._expanded.add(id);
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
      toast.success(messages.tag.created(data.name));
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

  async moveTag(id: string, newParentId: string | null): Promise<void> {
    try {
      const tag = this._findTag(id);
      const tagName = tag?.name || 'tag';

      await ApiClient.tag.moveTag({ id, newParentId });
      toast.success(messages.tag.moved(tagName));
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

  async deleteTag(id: string): Promise<void> {
    try {
      const tag = this._findTag(id);
      const tagName = tag?.name || 'tag';

      await ApiClient.tag.deleteTag(id);
      toast.success(messages.tag.deleted(tagName));
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

  async refetch(): Promise<void> {
    this._clearExpansion();
    await this.load({ page: this._meta.page || 1 });
  }

  private _findTag(id: string): Tag | undefined {
    const root = this._items.find((item) => item.id === id);
    if (root) {
      return root;
    }

    for (const children of this._childrenCache.values()) {
      const match = children.find((child) => child.id === id);
      if (match) {
        return match;
      }
    }

    return undefined;
  }

  private _clearExpansion(): void {
    this._expanded.clear();
    this._childrenCache.clear();
    this._loadingNodes.clear();
  }

  clear(): void {
    this._debouncedSearchLoad.cancel();
    this._items = [];
    this._searchTerm = '';
    this._clearExpansion();
    this._meta = {
      page: 1,
      size: this._config.defaultPageSize,
      total: 0,
    };
  }
}

const TAG_FEED = Symbol('TagFeed');

const tagFeed = new TagFeed();

export const useTagFeed = (
  func: ((state: TagFeed) => void) | undefined = undefined,
): TagFeed => {
  if (func) {
    func(tagFeed);
  }

  return useState<TagFeed>(TAG_FEED, tagFeed);
};

export { TagFeed };
