import type {
  CollectionItemsFeed,
  MediaItem,
} from '@slink/lib/state/CollectionItemsFeed.svelte';

export class CollectionImagesFeedAdapter {
  constructor(private _collectionFeed: CollectionItemsFeed) {}

  get items(): MediaItem[] {
    return this._collectionFeed.media;
  }

  get hasMore(): boolean {
    return this._collectionFeed.hasMore;
  }

  get isDirty(): boolean {
    return this._collectionFeed.isDirty;
  }

  updateItem(item: MediaItem, updates: Partial<MediaItem>): void {
    this._collectionFeed.updateItemMedia(item.id, updates);
  }

  async nextPage(): Promise<void> {
    await this._collectionFeed.nextPage();
  }
}
