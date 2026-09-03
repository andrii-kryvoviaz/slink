import { ApiClient } from '@slink/api';

import type { BookmarkItem } from '@slink/api/Response';

import type { MediaItem } from '@slink/lib/state/MediaFeedAdapter';
import type { MediaFeed } from '@slink/lib/state/MediaFeedAdapter';
import { AbstractPaginatedFeed } from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';
import type {
  LoadParams,
  PaginatedResponse,
  SearchParams,
} from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';
import { useState } from '@slink/lib/state/core/ContextAwareState';

class UserBookmarksFeed
  extends AbstractPaginatedFeed<BookmarkItem>
  implements MediaFeed
{
  public constructor() {
    super({
      defaultPageSize: 12,
      useCursor: true,
      appendMode: 'always',
    });
  }

  protected async fetchData(
    params: LoadParams & SearchParams,
  ): Promise<PaginatedResponse<BookmarkItem>> {
    const { limit = 12, cursor } = params;

    return ApiClient.bookmark.getUserBookmarks(limit, cursor);
  }

  protected _getItemId(item: BookmarkItem): string {
    return item.id;
  }

  public async removeBookmark(bookmark: BookmarkItem): Promise<void> {
    await ApiClient.bookmark.removeBookmark(bookmark.image.id);
    await this.removeItems([bookmark.id]);
  }

  public get media(): MediaItem[] {
    return this._items
      .map((bookmark) => bookmark.image)
      .filter((image): image is MediaItem => 'url' in image);
  }

  public getMediaIndex(imageId: string): number {
    return this.media.findIndex((image) => image.id === imageId);
  }

  public updateItemMedia(imageId: string, updates: Partial<MediaItem>): void {
    const bookmark = this._items.find((item) => item.image.id === imageId);
    if (!bookmark) return;

    if (updates.isBookmarked === false) {
      this.removeItems([bookmark.id]);
      return;
    }

    this.update(bookmark.id, { image: updates });
  }
}

const USER_BOOKMARKS_FEED = Symbol('UserBookmarksFeed');

const userBookmarksFeed = new UserBookmarksFeed();

export const useUserBookmarksFeed = (): UserBookmarksFeed => {
  return useState<UserBookmarksFeed>(USER_BOOKMARKS_FEED, userBookmarksFeed);
};
