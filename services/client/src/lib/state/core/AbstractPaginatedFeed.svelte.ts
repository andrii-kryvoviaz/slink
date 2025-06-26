import type { RequestStateOptions } from '@slink/lib/state/core/AbstractHttpState.svelte';

import { AbstractHttpState } from '@slink/lib/state/core/AbstractHttpState.svelte';

export interface PaginationMetadata {
  page: number;
  size: number;
  total: number;
  nextCursor?: string | null;
  prevCursor?: string | null;
}

export interface PaginatedResponse<T> {
  data: T[];
  meta: PaginationMetadata;
}

export interface LoadParams {
  page?: number;
  limit?: number;
  cursor?: string;
}

export interface SearchParams {
  searchTerm?: string;
  searchBy?: string;
}

export interface PaginationConfig {
  defaultPageSize: number;
  useCursor: boolean;
  appendMode: 'auto' | 'always' | 'never';
}

export abstract class AbstractPaginatedFeed<T> extends AbstractHttpState<
  PaginatedResponse<T>
> {
  protected _items: T[] = $state([]);
  protected _meta: PaginationMetadata = $state({} as PaginationMetadata);
  protected _nextCursor: string | null = $state(null);
  protected _config: PaginationConfig;

  protected constructor(config: Partial<PaginationConfig> = {}) {
    super();
    this._config = {
      defaultPageSize: 12,
      useCursor: true,
      appendMode: 'auto',
      ...config,
    };
    this.reset();
  }

  protected abstract fetchData(
    params: LoadParams & SearchParams,
  ): Promise<PaginatedResponse<T>>;

  public reset(): void {
    this.markDirty(false);
    this._items = [];
    this._nextCursor = null;
    this._meta = {
      page: 1,
      size: this._config.defaultPageSize,
      total: 0,
    };
  }

  public async load(
    params: LoadParams & SearchParams = {},
    options?: RequestStateOptions,
  ): Promise<void> {
    const {
      page = this._meta.page,
      limit = this._meta.size,
      cursor = this._config.useCursor
        ? this._nextCursor || undefined
        : undefined,
      ...searchParams
    } = params;

    const isInitialLoad = page === 1 && !cursor;
    const shouldAppend = this._shouldAppendItems(isInitialLoad);

    await this.fetch(
      () => this.fetchData({ page, limit, cursor, ...searchParams }),
      (response) => {
        if (shouldAppend) {
          this._items = this._items.concat(
            response.data.filter((item) => !this._hasItem(item)),
          );
        } else {
          this._items = response.data;
        }

        this._meta = response.meta;
        this._nextCursor = response.meta.nextCursor || null;
      },
      options,
    );
  }

  public async nextPage(options?: RequestStateOptions): Promise<void> {
    if (!this.hasMore) {
      return;
    }

    if (this._config.useCursor && this._nextCursor) {
      await this.load(
        {
          page: this._meta.page,
          limit: this._meta.size,
          cursor: this._nextCursor,
        },
        options,
      );
    } else {
      await this.load(
        { page: this._meta.page + 1, limit: this._meta.size },
        options,
      );
    }
  }

  public addItem(item: T): void {
    this._items = [item, ...this._items];
  }

  public replaceItem(item: T): void {
    const index = this._findItemIndex(item);
    if (index !== -1) {
      this._items[index] = item;
    }
  }

  public updateItem(item: T, updates: Partial<T>): void {
    const index = this._findItemIndex(item);
    if (index !== -1) {
      this._items[index] = { ...this._items[index], ...updates };
    }
  }

  public removeItem(identifier: string | T): void {
    const itemToRemove =
      typeof identifier === 'string'
        ? this._items.find((item) => this._getItemId(item) === identifier)
        : identifier;

    if (!itemToRemove) return;

    this._items = this._items.filter(
      (item) => this._getItemId(item) !== this._getItemId(itemToRemove),
    );

    if (this._items.length === 0) {
      this.reset();
    }
  }

  get hasMore(): boolean {
    if (this._config.useCursor && this._meta.nextCursor !== undefined) {
      return this._meta.nextCursor !== null;
    }

    return this._meta.page < Math.ceil(this._meta.total / this._meta.size);
  }

  get items(): T[] {
    return this._items;
  }

  get meta(): PaginationMetadata {
    return this._meta;
  }

  get hasItems(): boolean {
    return this._items.length > 0;
  }

  get isEmpty(): boolean {
    return !this.hasItems && this.isDirty;
  }

  protected abstract _getItemId(item: T): string;

  protected _hasItem(item: T): boolean {
    const itemId = this._getItemId(item);
    return this._items.some((existing) => this._getItemId(existing) === itemId);
  }

  protected _findItemIndex(item: T): number {
    const itemId = this._getItemId(item);
    return this._items.findIndex(
      (existing) => this._getItemId(existing) === itemId,
    );
  }

  protected _shouldAppendItems(isInitialLoad: boolean): boolean {
    switch (this._config.appendMode) {
      case 'always':
        return true;
      case 'never':
        return false;
      case 'auto':
      default:
        return !isInitialLoad;
    }
  }
}
