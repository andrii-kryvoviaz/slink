import { ApiClient } from '@slink/api/Client';
import type { Tag } from '@slink/api/Resources/TagResource';

import { UrlParamManager } from '../url/urlParams';

export interface TagFilterParams {
  tagIds: string[];
  requireAllTags: boolean;
}

export interface TagFilterUrlConfig {
  replaceState?: boolean;
  noScroll?: boolean;
}

export class TagFilterUrlManager {
  private urlManager: UrlParamManager;

  constructor(urlManager: UrlParamManager) {
    this.urlManager = urlManager;
  }

  static fromPageUrl(pageUrl: URL): TagFilterUrlManager {
    return new TagFilterUrlManager(UrlParamManager.fromPageUrl(pageUrl));
  }

  getTagFilterParams(): TagFilterParams {
    return {
      tagIds: this.urlManager.getArray('tagIds'),
      requireAllTags: this.urlManager.getBoolean('requireAllTags'),
    };
  }

  setTagFilterParams(tags: Tag[], requireAllTags: boolean): string {
    this.urlManager.setArray(
      'tagIds',
      tags.map((tag) => tag.id),
    );

    if (requireAllTags && tags.length > 0) {
      this.urlManager.set('requireAllTags', 'true');
    } else {
      this.urlManager.delete('requireAllTags');
    }

    return this.urlManager.buildUrl();
  }

  clearTagFilter(): string {
    this.urlManager.delete('tagIds').delete('requireAllTags');
    return this.urlManager.buildUrl();
  }

  hasTagFilter(): boolean {
    return this.urlManager.has('tagIds');
  }

  buildHistoryFilterUrl(tag: Tag, requireAllTags = false): string {
    const manager = new UrlParamManager('/history');
    manager.setArray('tagIds', [tag.id]);

    if (requireAllTags) {
      manager.set('requireAllTags', 'true');
    }

    return manager.buildUrl();
  }

  buildHistoryFilterUrlMultiple(tags: Tag[], requireAllTags = false): string {
    const manager = new UrlParamManager('/history');
    manager.setArray(
      'tagIds',
      tags.map((tag) => tag.id),
    );

    if (requireAllTags && tags.length > 0) {
      manager.set('requireAllTags', 'true');
    }

    return manager.buildUrl();
  }
}

export class TagFilterLoader {
  async loadTagsById(tagIds: string[]): Promise<Tag[]> {
    if (tagIds.length === 0) return [];

    return await ApiClient.tag.getByIds(tagIds);
  }

  async loadTagsFromUrlParams(urlManager: TagFilterUrlManager): Promise<{
    tags: Tag[];
    requireAllTags: boolean;
  }> {
    const params = urlManager.getTagFilterParams();

    const tags =
      params.tagIds.length > 0 ? await this.loadTagsById(params.tagIds) : [];

    return {
      tags,
      requireAllTags: params.requireAllTags,
    };
  }
}

export const tagFilterUtils = {
  fromPageUrl: (pageUrl: URL) => TagFilterUrlManager.fromPageUrl(pageUrl),
  createLoader: () => new TagFilterLoader(),
  buildHistoryUrl: (tag: Tag, requireAllTags = false) =>
    new TagFilterUrlManager(
      new UrlParamManager('/history'),
    ).buildHistoryFilterUrl(tag, requireAllTags),
  buildHistoryUrlMultiple: (tags: Tag[], requireAllTags = false) =>
    new TagFilterUrlManager(
      new UrlParamManager('/history'),
    ).buildHistoryFilterUrlMultiple(tags, requireAllTags),
};
