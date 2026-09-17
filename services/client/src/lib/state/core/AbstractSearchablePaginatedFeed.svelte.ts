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
    await super.load(this._withSearch(params), options);
  }

  public override async reload(
    params: LoadParams & ExtendedSearchParams = {},
    options?: RequestStateOptions,
  ): Promise<void> {
    await super.reload(this._withSearch(params), options);
  }

  private _withSearch(
    params: LoadParams & ExtendedSearchParams,
  ): LoadParams & ExtendedSearchParams {
    let searchTerm = params.searchTerm;
    if (searchTerm === undefined) {
      searchTerm = this._searchTerm.trim() || undefined;
    }

    let searchBy: string | undefined;
    if (searchTerm) {
      searchBy = params.searchBy ?? this._searchBy;
    }

    return { ...params, searchTerm, searchBy };
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
