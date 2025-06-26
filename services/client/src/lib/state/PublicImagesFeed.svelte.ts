import type {
  LoadParams,
  PaginatedResponse,
} from './core/AbstractPaginatedFeed.svelte';
import type { ImageListingItem } from '@slink/api/Response';
import type { ExtendedSearchParams } from '@slink/lib/state/core/AbstractSearchablePaginatedFeed.svelte';

import { ApiClient } from '@slink/api/Client';

import { AbstractSearchablePaginatedFeed } from '@slink/lib/state/core/AbstractSearchablePaginatedFeed.svelte';
import { useState } from '@slink/lib/state/core/ContextAwareState';

class PublicImagesFeed extends AbstractSearchablePaginatedFeed<ImageListingItem> {
  public constructor() {
    super(
      {
        defaultPageSize: 12,
        useCursor: true,
        appendMode: 'auto',
      },
      'user',
    );
  }

  protected async fetchData(
    params: LoadParams & ExtendedSearchParams,
  ): Promise<PaginatedResponse<ImageListingItem>> {
    const { page = 1, limit = 12, cursor, searchTerm, searchBy } = params;

    return ApiClient.image.getPublicImages(
      page,
      limit,
      'attributes.createdAt',
      searchTerm,
      searchBy,
      cursor,
    );
  }

  protected _getItemId(item: ImageListingItem): string {
    return item.id;
  }

  protected _getDefaultSearchBy(): string {
    return 'user';
  }
}

const PUBLIC_IMAGES_FEED = Symbol('PublicImagesFeed');

const publicImagesFeed = new PublicImagesFeed();

export const usePublicImagesFeed = (): PublicImagesFeed => {
  return useState<PublicImagesFeed>(PUBLIC_IMAGES_FEED, publicImagesFeed);
};
