import { ApiClient } from '@slink/api';
import { getContext, hasContext, setContext } from 'svelte';

import type {
  ShareExpiryFilter,
  ShareListItemResponse,
  ShareListingResponse,
  ShareProtectionFilter,
  ShareTypeFilter,
} from '@slink/api/Response/Share/ShareListItemResponse';
import type { ShareableType } from '@slink/api/Response/Share/ShareResponse';

import { AbstractPaginatedFeed } from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';
import type {
  LoadParams,
  PaginatedResponse,
  SearchParams,
} from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';

import { debounce } from '@slink/utils/time/debounce';

const SHARES_FEED_CONTEXT = Symbol('SharesFeed');

export interface SharesFeedScope {
  shareableId?: string;
  shareableType?: ShareableType;
}

class Filter<T> {
  value: T = $state() as T;

  constructor(private readonly _default: T) {
    this.value = _default;
  }

  get isActive(): boolean {
    return this.value !== this._default;
  }

  apply(next: T | undefined): boolean {
    if (next === undefined || this.value === next) return false;
    this.value = next;
    return true;
  }

  reset(): void {
    this.value = this._default;
  }
}

type FilterValues = {
  expiry: ShareExpiryFilter;
  protection: ShareProtectionFilter;
  type: ShareTypeFilter;
  search: string;
};

export class SharesFeed extends AbstractPaginatedFeed<ShareListItemResponse> {
  readonly filters = {
    expiry: new Filter<ShareExpiryFilter>('any'),
    protection: new Filter<ShareProtectionFilter>('any'),
    type: new Filter<ShareTypeFilter>('all'),
    search: new Filter<string>(''),
  } as const;

  readonly activeFilterCount = $derived(
    Object.values(this.filters).filter((f) => f.isActive).length,
  );
  readonly hasActiveFilters = $derived(this.activeFilterCount > 0);

  private readonly _debouncedLoad = debounce(() => this.load(), 300);
  private _scope: SharesFeedScope = $state({});

  public constructor() {
    super({
      defaultPageSize: 20,
      useCursor: true,
      appendMode: 'always',
    });
  }

  override get key() {
    return 'shares' as const;
  }

  protected async fetchData(
    params: LoadParams & SearchParams,
  ): Promise<PaginatedResponse<ShareListItemResponse>> {
    const { cursor, limit = this._config.defaultPageSize, searchTerm } = params;
    const { expiry, protection, type, search } = this.filters;

    const response: ShareListingResponse = await ApiClient.share.list({
      limit,
      cursor,
      searchTerm: searchTerm ?? search.value ?? undefined,
      expiry: expiry.value,
      protection: protection.value,
      type: type.value,
      shareableId: this._scope.shareableId,
      shareableType: this._scope.shareableType,
    });

    return response;
  }

  get scope(): SharesFeedScope {
    return this._scope;
  }

  applyFilters(patch: Partial<FilterValues>): void {
    const { expiry, protection, type, search } = this.filters;

    const changed = [
      expiry.apply(patch.expiry),
      protection.apply(patch.protection),
      type.apply(patch.type),
      search.apply(patch.search),
    ].some(Boolean);

    if (!changed) return;

    const isSearchOnly = Object.keys(patch).length === 1 && 'search' in patch;

    if (isSearchOnly) {
      this._debouncedLoad();
      return;
    }

    this._debouncedLoad.cancel();
    this.load('search' in patch ? { searchTerm: patch.search } : undefined);
  }

  resetFilters(): void {
    if (!this.hasActiveFilters) return;
    Object.values(this.filters).forEach((f) => f.reset());
    this._debouncedLoad.cancel();
    this.load({ searchTerm: '' });
  }

  setScope(scope: SharesFeedScope): void {
    if (
      this._scope.shareableId === scope.shareableId &&
      this._scope.shareableType === scope.shareableType
    ) {
      return;
    }
    this._scope = { ...scope };
    this.reset();
  }

  protected _getItemId(item: ShareListItemResponse): string {
    return item.shareId;
  }

  public applyExpirationChange(
    shareId: string,
    expiresAt: string | null,
  ): void {
    const current = this.get(shareId);
    if (!current) return;
    if (current.expiresAt === expiresAt) return;

    const isExpired =
      expiresAt !== null && new Date(expiresAt).getTime() < Date.now();

    this.update(shareId, { expiresAt, isExpired });
  }

  public applyPasswordChange(shareId: string, requiresPassword: boolean): void {
    const current = this.get(shareId);
    if (!current) return;
    if (current.requiresPassword === requiresPassword) return;

    this.update(shareId, { requiresPassword });
  }

  public applyUnpublished(shareId: string): void {
    this.removeItem(shareId);
  }
}

export function provideSharesFeed(): SharesFeed {
  const feed = new SharesFeed();
  setContext(SHARES_FEED_CONTEXT, feed);
  return feed;
}

export function getSharesFeed(): SharesFeed {
  if (!hasContext(SHARES_FEED_CONTEXT)) {
    throw new Error(
      'SharesFeed is not available in context. Call provideSharesFeed() in an ancestor component.',
    );
  }
  return getContext<SharesFeed>(SHARES_FEED_CONTEXT);
}
