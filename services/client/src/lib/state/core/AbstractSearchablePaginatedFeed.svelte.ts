import type { RequestStateOptions } from '@slink/lib/state/core/AbstractHttpState.svelte';
import type {
  LoadParams,
  PaginationConfig,
  SearchParams,
} from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';
import { AbstractPaginatedFeed } from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';

export interface ExtendedSearchParams extends SearchParams {
  searchTerm?: string;
  searchBy?: string;
}

export abstract class AbstractSearchablePaginatedFeed<
  T,
> extends AbstractPaginatedFeed<T> {
  protected _searchTerm: string = $state('');
  protected _searchBy: string = $state('');

  protected constructor(
    config: Partial<PaginationConfig> = {},
    defaultSearchBy: string = 'user',
  ) {
    super(config);
    this._searchBy = defaultSearchBy;
  }

  public override reset(): void {
    super.reset();
  }

  public resetSearch(): void {
    this._searchTerm = '';
    this._searchBy = this._getDefaultSearchBy();
    super.reset();
  }

  public setSearch(searchTerm: string, searchBy?: string): void {
    const hasChanged =
      this._searchTerm !== searchTerm ||
      (searchBy && this._searchBy !== searchBy);

    this._searchTerm = searchTerm;
    if (searchBy) {
      this._searchBy = searchBy;
    }

    if (hasChanged) {
      super.reset();
    }
  }

  public async search(
    searchTerm: string,
    searchBy?: string,
    options?: RequestStateOptions,
  ): Promise<void> {
    this.setSearch(searchTerm, searchBy);
    await this.load({ page: 1 }, options);
  }

  public override async load(
    params: LoadParams & ExtendedSearchParams = {},
    options?: RequestStateOptions,
  ): Promise<void> {
    const searchTerm =
      params.searchTerm ?? (this._searchTerm.trim() || undefined);
    const searchBy = searchTerm
      ? (params.searchBy ?? this._searchBy)
      : undefined;

    await super.load(
      {
        ...params,
        searchTerm,
        searchBy,
      },
      options,
    );
  }

  get searchTerm(): string {
    return this._searchTerm;
  }

  get searchBy(): string {
    return this._searchBy;
  }

  get isSearching(): boolean {
    return this._searchTerm.trim().length > 0;
  }

  protected abstract _getDefaultSearchBy(): string;
}
