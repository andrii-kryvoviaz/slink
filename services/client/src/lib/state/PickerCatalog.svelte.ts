import { ApiClient } from '@slink/api';

import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';

import type { Tag } from '@slink/api/Resources/TagResource';
import type { CollectionResponse } from '@slink/api/Response';

import { messages } from '@slink/lib/utils/i18n/messages/toast.language';

export const createTag = (name: string, parentId?: string): Promise<Tag> =>
  ApiClient.tag
    .create({ name, parentId })
    .then(({ id }) => ApiClient.tag.getById(id));

export const createCollection = (data: {
  name: string;
  description?: string;
}): Promise<CollectionResponse> => ApiClient.collection.create(data);

export interface PickerCatalogDeps<TItem extends { id: string }> {
  fetch: () => Promise<TItem[]>;
  create: (name: string) => Promise<TItem>;
  onLoadError: () => void;
}

export class PickerCatalog<TItem extends { id: string }> {
  protected _items: TItem[] = $state.raw([]);
  protected _isLoading: boolean = $state(false);
  protected _isLoaded: boolean = $state(false);

  protected _deps: PickerCatalogDeps<TItem>;

  constructor(deps: PickerCatalogDeps<TItem>) {
    this._deps = deps;
  }

  get items() {
    return this._items;
  }

  get isLoading() {
    return this._isLoading;
  }

  get isLoaded() {
    return this._isLoaded;
  }

  get isEmpty() {
    return this._items.length === 0;
  }

  async load() {
    if (this._isLoaded) return;

    this._isLoading = true;
    try {
      this._items = await this._deps.fetch();
      this._isLoaded = true;
    } catch {
      this._deps.onLoadError();
    } finally {
      this._isLoading = false;
    }
  }

  async create(name: string): Promise<TItem> {
    const item = await this._deps.create(name);
    this.addItem(item);
    return item;
  }

  addItem(item: TItem) {
    this._items = [item, ...this._items];
  }

  reset() {
    this._items = [];
    this._isLoading = false;
    this._isLoaded = false;
  }
}

function flattenTags(tags: Tag[]): Tag[] {
  const seen = new Set<string>();
  const result: Tag[] = [];

  const flatten = (items: Tag[]) => {
    for (const tag of items) {
      if (!seen.has(tag.id)) {
        seen.add(tag.id);
        result.push(tag);
      }
      if (tag.children && tag.children.length > 0) {
        flatten(tag.children);
      }
    }
  };

  flatten(tags);
  return result;
}

async function fetchAllCollections(): Promise<CollectionResponse[]> {
  const collections: CollectionResponse[] = [];
  let cursor: string | undefined;

  do {
    const response = await ApiClient.collection.getList(50, cursor);
    collections.push(...response.data);
    cursor = response.meta.nextCursor;
  } while (cursor);

  return collections;
}

export function createTagCatalog(): PickerCatalog<Tag> {
  return new PickerCatalog<Tag>({
    fetch: () =>
      ApiClient.tag
        .getList({
          limit: 100,
          includeChildren: true,
          orderBy: 'path',
          order: 'asc',
        })
        .then((response) => flattenTags(response.data)),
    create: (name) => createTag(name),
    onLoadError: () => toast.error(messages.tag.failedToLoad),
  });
}

export function createCollectionCatalog(): PickerCatalog<CollectionResponse> {
  return new PickerCatalog<CollectionResponse>({
    fetch: () => fetchAllCollections(),
    create: (name) => createCollection({ name }),
    onLoadError: () => toast.error(messages.collection.failedToLoad),
  });
}
