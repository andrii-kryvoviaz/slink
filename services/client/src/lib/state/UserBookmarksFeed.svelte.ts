import { ApiClient } from '@slink/api/Client';
import type { BookmarkItem } from '@slink/api/Response';

import { AbstractPaginatedFeed } from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';
import type {
  LoadParams,
  PaginatedResponse,
  SearchParams,
} from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';
import { useState } from '@slink/lib/state/core/ContextAwareState';

class UserBookmarksFeed extends AbstractPaginatedFeed<BookmarkItem> {
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
    const { page = 1, limit = 12, cursor } = params;

    return ApiClient.bookmark.getUserBookmarks(page, limit, cursor);
  }

  protected _getItemId(item: BookmarkItem): string {
    return item.id;
  }
}

const USER_BOOKMARKS_FEED = Symbol('UserBookmarksFeed');

const userBookmarksFeed = new UserBookmarksFeed();

export const useUserBookmarksFeed = (): UserBookmarksFeed => {
  return useState<UserBookmarksFeed>(USER_BOOKMARKS_FEED, userBookmarksFeed);
};
