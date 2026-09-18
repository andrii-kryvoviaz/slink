import { replaceUrl } from '@slink/utils/navigation';
import type { NavigationConfig } from '@slink/utils/navigation';
import type { SearchBy, SearchFilter } from '@slink/utils/url';
import { UrlParamManager } from '@slink/utils/url';

export interface SearchFilterManager {
  read(): SearchFilter;
  updateUrl(
    searchTerm: string,
    searchBy: SearchBy,
    navigationConfig?: NavigationConfig,
  ): Promise<void>;
  clearUrl(navigationConfig?: NavigationConfig): Promise<void>;
  hasFilterInUrl(): boolean;
}

export class UrlSearchFilterManager implements SearchFilterManager {
  _urlManager: UrlParamManager;

  constructor(urlManager: UrlParamManager) {
    this._urlManager = urlManager;
  }

  static fromPageUrl(pageUrl: URL): UrlSearchFilterManager {
    return new UrlSearchFilterManager(UrlParamManager.fromPageUrl(pageUrl));
  }

  read(): SearchFilter {
    return this._urlManager.searchFilter();
  }

  hasFilterInUrl(): boolean {
    return this.read().searchTerm !== undefined;
  }

  async updateUrl(
    searchTerm: string,
    searchBy: SearchBy,
    navigationConfig?: NavigationConfig,
  ): Promise<void> {
    const term = searchTerm.trim();

    await this._navigateIfChanged(navigationConfig, () => {
      if (term) {
        this._urlManager.set('search', term).set('searchBy', searchBy);
      } else {
        this._urlManager.delete('search').delete('searchBy');
      }
    });
  }

  async clearUrl(navigationConfig?: NavigationConfig): Promise<void> {
    await this._navigateIfChanged(navigationConfig, () => {
      this._urlManager.delete('search').delete('searchBy');
    });
  }

  async _navigateIfChanged(
    navigationConfig: NavigationConfig | undefined,
    mutate: () => void,
  ): Promise<void> {
    const before = this._urlManager.toSearchParams();
    mutate();
    const after = this._urlManager.toSearchParams();

    const isUnchanged =
      (after.get('search') ?? '') === (before.get('search') ?? '') &&
      (after.get('searchBy') ?? '') === (before.get('searchBy') ?? '');

    if (isUnchanged) return;

    await replaceUrl(this._urlManager.buildUrl(), {
      keepFocus: true,
      noScroll: true,
      ...navigationConfig,
    });
  }
}

export const createSearchFilterManager = (pageUrl: URL): SearchFilterManager =>
  UrlSearchFilterManager.fromPageUrl(pageUrl);
