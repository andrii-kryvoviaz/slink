import type { Tag } from '@slink/api/Resources/TagResource';

import { replaceUrl } from '@slink/utils/navigation';
import type { NavigationConfig } from '@slink/utils/navigation';
import {
  TagFilterLoader,
  TagFilterUrlManager,
} from '@slink/utils/tag/tagFilterUrl';

export interface TagFilterState {
  tags: Tag[];
  requireAllTags: boolean;
}

export interface TagFilterManager {
  loadFromUrl(): Promise<TagFilterState>;
  updateUrl(tags: Tag[], requireAllTags: boolean): Promise<void>;
  clearUrl(): Promise<void>;
  hasFiltersInUrl(): boolean;
}

export class UrlTagFilterManager implements TagFilterManager {
  private urlManager: TagFilterUrlManager;
  private tagLoader: TagFilterLoader;

  constructor(urlManager: TagFilterUrlManager, tagLoader: TagFilterLoader) {
    this.urlManager = urlManager;
    this.tagLoader = tagLoader;
  }

  static fromPageUrl(pageUrl: URL): UrlTagFilterManager {
    return new UrlTagFilterManager(
      TagFilterUrlManager.fromPageUrl(pageUrl),
      new TagFilterLoader(),
    );
  }

  async loadFromUrl(): Promise<TagFilterState> {
    return this.tagLoader.loadTagsFromUrlParams(this.urlManager);
  }

  async updateUrl(
    tags: Tag[],
    requireAllTags: boolean,
    navigationConfig?: NavigationConfig,
  ): Promise<void> {
    const url = this.urlManager.setTagFilterParams(tags, requireAllTags);
    await replaceUrl(url, navigationConfig);
  }

  async clearUrl(navigationConfig?: NavigationConfig): Promise<void> {
    const url = this.urlManager.clearTagFilter();
    await replaceUrl(url, navigationConfig);
  }

  hasFiltersInUrl(): boolean {
    return this.urlManager.hasTagFilter();
  }
}

export const createTagFilterManager = (pageUrl: URL): TagFilterManager =>
  UrlTagFilterManager.fromPageUrl(pageUrl);
