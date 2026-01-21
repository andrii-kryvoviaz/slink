import type { ImageListingItem } from '@slink/api/Response';

import type { CollectionItemsFeed } from '@slink/lib/state/CollectionItemsFeed.svelte';

export class CollectionImagesFeedAdapter {
  constructor(private _collectionFeed: CollectionItemsFeed) {}

  get items(): ImageListingItem[] {
    return this._collectionFeed.images;
  }

  get hasMore(): boolean {
    return this._collectionFeed.hasMore;
  }

  get isDirty(): boolean {
    return this._collectionFeed.isDirty;
  }

  updateItem(item: ImageListingItem, updates: Partial<ImageListingItem>): void {
    this._collectionFeed.updateImage(item, updates);
  }

  async nextPage(): Promise<void> {
    await this._collectionFeed.nextPage();
  }
}
