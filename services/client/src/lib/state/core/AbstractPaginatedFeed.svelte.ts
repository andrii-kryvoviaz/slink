import { SvelteMap } from 'svelte/reactivity';

import { AbstractHttpState } from '@slink/lib/state/core/AbstractHttpState.svelte';
import type { RequestStateOptions } from '@slink/lib/state/core/AbstractHttpState.svelte';
import { SkeletonManager } from '@slink/lib/state/core/SkeletonConfig.svelte';

import { deepMerge } from '@slink/utils/object/deepMerge';

export interface PaginationMetadata {
  page?: number;
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

type DeepPartial<T> = {
  [P in keyof T]?: T[P] extends object ? DeepPartial<T[P]> : T[P];
};

export abstract class AbstractPaginatedFeed<T> extends AbstractHttpState<
  PaginatedResponse<T>
> {
  private _itemMap: SvelteMap<string, T> = new SvelteMap();
  private _order: string[] = $state([]);
  protected _meta: PaginationMetadata = $state({} as PaginationMetadata);
  protected _nextCursor: string | null = $state(null);
  protected _config: PaginationConfig;
  protected _skeletonManager = new SkeletonManager();

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
    this._itemMap.clear();
    this._order = [];
    this._nextCursor = null;
    this._skeletonManager.reset();
    this._meta = {
      page: this._config.useCursor ? undefined : 1,
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

    const isInitialLoad = this._config.useCursor ? !cursor : page === 1;
    const shouldAppend = this._shouldAppendItems(isInitialLoad);

    if (isInitialLoad && this._itemMap.size === 0) {
      this._skeletonManager.show();
    }

    await this.fetch(
      () => this.fetchData({ page, limit, cursor, ...searchParams }),
      (response) => {
        if (shouldAppend) {
          for (const item of response.data) {
            const id = this._getItemId(item);
            if (!this._itemMap.has(id)) {
              this._itemMap.set(id, item);
              this._order = [...this._order, id];
            }
          }
        } else {
          this._itemMap.clear();
          this._order = [];
          for (const item of response.data) {
            const id = this._getItemId(item);
            this._itemMap.set(id, item);
            this._order = [...this._order, id];
          }
        }
        this._meta = response.meta;
        this._nextCursor = response.meta.nextCursor || null;

        if (isInitialLoad) {
          this._skeletonManager.hide();
        }
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
          limit: this._meta.size,
          cursor: this._nextCursor,
        },
        options,
      );
    } else {
      await this.load(
        { page: (this._meta.page ?? 0) + 1, limit: this._meta.size },
        options,
      );
    }
  }

  public addItem(item: T): void {
    const id = this._getItemId(item);
    this._itemMap.set(id, item);
    this._order = [id, ...this._order];
  }

  public replaceItem(item: T): void {
    const id = this._getItemId(item);
    if (this._itemMap.has(id)) {
      this._itemMap.set(id, item);
    }
  }

  public updateItem(item: T, updates: DeepPartial<T>): void {
    this.update(this._getItemId(item), updates);
  }

  public update(id: string, updates: DeepPartial<T>): boolean {
    const existing = this._itemMap.get(id);
    if (!existing) return false;

    const needsDeepMerge = Object.values(updates).some(
      (value) =>
        value !== null && typeof value === 'object' && !Array.isArray(value),
    );

    this._itemMap.set(
      id,
      needsDeepMerge
        ? (deepMerge(existing, updates) as T)
        : { ...existing, ...(updates as Partial<T>) },
    );
    return true;
  }

  public get(id: string): T | undefined {
    return this._itemMap.get(id);
  }

  public has(id: string): boolean {
    return this._itemMap.has(id);
  }

  public removeItem(identifier: string | T): void {
    const id =
      typeof identifier === 'string' ? identifier : this._getItemId(identifier);
    if (!this._itemMap.has(id)) return;

    this._itemMap.delete(id);
    this._order = this._order.filter((orderId) => orderId !== id);

    if (this._itemMap.size === 0) {
      this.reset();
    }
  }

  get hasMore(): boolean {
    if (this._config.useCursor) {
      return !!this._nextCursor;
    }

    return (
      (this._meta.page ?? 0) < Math.ceil(this._meta.total / this._meta.size)
    );
  }

  get items(): T[] {
    return this._order.map((id) => this._itemMap.get(id)!);
  }

  protected get _items(): T[] {
    return this.items;
  }

  protected set _items(newItems: T[]) {
    this._itemMap.clear();
    this._order = [];
    for (const item of newItems) {
      const id = this._getItemId(item);
      this._itemMap.set(id, item);
      this._order.push(id);
    }
  }

  get meta(): PaginationMetadata {
    return this._meta;
  }

  get hasItems(): boolean {
    return this._itemMap.size > 0;
  }

  get isEmpty(): boolean {
    return !this.hasItems && this.isDirty;
  }

  get showSkeleton(): boolean {
    const isInitialState = !this.isDirty && !this.hasItems;
    return (
      (this._skeletonManager.isVisible || isInitialState) &&
      !this.isEmpty &&
      !this.hasError
    );
  }

  public configureSkeleton(
    config: Parameters<SkeletonManager['configure']>[0],
  ) {
    this._skeletonManager.configure(config);
  }

  protected abstract _getItemId(item: T): string;

  protected _hasItem(item: T): boolean {
    return this._itemMap.has(this._getItemId(item));
  }

  protected _findItemIndex(item: T): number {
    return this._order.indexOf(this._getItemId(item));
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
