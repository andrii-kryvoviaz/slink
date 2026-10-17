import { ApiClient } from '@slink/api';

import { toast } from '#lib/utils/ui/toast-sonner.svelte.js';

import type { Tag } from '@slink/api/Resources/TagResource';
import type { CollectionResponse } from '@slink/api/Response';

import { messages } from '@slink/lib/utils/i18n/messages/toast.language';
import { debounce } from '@slink/lib/utils/time/debounce';

export const createTag = (name: string, parentId?: string): Promise<Tag> =>
  ApiClient.tag
    .create({ name, parentId })
    .then(({ id }) => ApiClient.tag.getById(id));

export const searchTags = (searchTerm: string): Promise<Tag[]> =>
  ApiClient.tag
    .getList({ searchTerm, limit: 50, orderBy: 'path', order: 'asc' })
    .then((response) => response.data);

export const searchCollections = (
  searchTerm: string,
): Promise<CollectionResponse[]> =>
  ApiClient.collection
    .getList(50, undefined, searchTerm)
    .then((response) => response.data);

export const createCollection = (data: {
  name: string;
  description?: string;
}): Promise<CollectionResponse> => ApiClient.collection.create(data);

export interface PickerCatalogDeps<TItem extends { id: string }> {
  fetch: (searchTerm: string) => Promise<TItem[]>;
  create: (name: string) => Promise<TItem>;
  onLoadError: () => void;
}

const SEARCH_DEBOUNCE_MS = 300;

export class PickerCatalog<TItem extends { id: string }> {
  protected _items: TItem[] = $state.raw([]);
  protected _results: TItem[] | null = $state.raw(null);
  protected _isLoading: boolean = $state(false);
  protected _isSearching: boolean = $state(false);
  protected _isLoaded: boolean = $state(false);

  protected _deps: PickerCatalogDeps<TItem>;
  protected _term: string = '';
  protected _known: Map<string, TItem> = new Map();
  protected _debouncedSearch: ReturnType<
    typeof debounce<(term: string) => void>
  >;

  constructor(deps: PickerCatalogDeps<TItem>) {
    this._deps = deps;
    this._debouncedSearch = debounce((term: string) => {
      void this._runSearch(term);
    }, SEARCH_DEBOUNCE_MS);
  }

  get items() {
    return this._results ?? this._items;
  }

  get isLoading() {
    return this._isLoading || this._isSearching;
  }

  get isLoaded() {
    return this._isLoaded;
  }

  async load() {
    this._clearSearch();

    if (this._isLoaded) return;

    this._isLoading = true;
    try {
      const items = await this._deps.fetch('');
      this._remember(items);
      this._items = items;
      this._isLoaded = true;
    } catch {
      this._deps.onLoadError();
    } finally {
      this._isLoading = false;
    }
  }

  search(term: string) {
    const next = term.trim();
    if (next === this._term) return;

    this._debouncedSearch.cancel();
    this._term = next;

    if (!next) {
      this._results = null;
      this._isSearching = false;
      return;
    }

    this._isSearching = true;
    this._debouncedSearch(next);
  }

  resolve(ids: string[]): TItem[] {
    return ids
      .map((id) => this._known.get(id))
      .filter((item): item is TItem => item !== undefined);
  }

  async create(name: string): Promise<TItem> {
    const item = await this._deps.create(name);
    this.addItem(item);
    return item;
  }

  addItem(item: TItem) {
    this._known.set(item.id, item);
    this._items = [item, ...this._items];
    if (this._results) {
      this._results = [item, ...this._results];
    }
  }

  reset() {
    this._clearSearch();
    this._items = [];
    this._known = new Map();
    this._isLoading = false;
    this._isLoaded = false;
  }

  protected _clearSearch() {
    this._debouncedSearch.cancel();
    this._term = '';
    this._results = null;
    this._isSearching = false;
  }

  protected _remember(items: TItem[]) {
    for (const item of items) {
      this._known.set(item.id, item);
    }
  }

  protected async _runSearch(term: string) {
    try {
      const results = await this._deps.fetch(term);
      if (term !== this._term) return;
      this._remember(results);
      this._results = results;
    } catch {
      if (term !== this._term) return;
      this._deps.onLoadError();
      this._results = [];
    }
    this._isSearching = false;
  }
}

export function createTagCatalog(): PickerCatalog<Tag> {
  return new PickerCatalog<Tag>({
    fetch: searchTags,
    create: (name) => createTag(name),
    onLoadError: () => toast.error(messages.tag.failedToLoad),
  });
}

export function createCollectionCatalog(): PickerCatalog<CollectionResponse> {
  return new PickerCatalog<CollectionResponse>({
    fetch: searchCollections,
    create: (name) => createCollection({ name }),
    onLoadError: () => toast.error(messages.collection.failedToLoad),
  });
}
