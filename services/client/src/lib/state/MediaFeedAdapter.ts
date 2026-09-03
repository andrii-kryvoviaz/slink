import type { ImageListingItem } from '@slink/api/Response';

import type { PostViewerFeed } from '@slink/lib/state/PostViewerState.svelte';

export type MediaItem = ImageListingItem;

export interface MediaFeed {
  media: MediaItem[];
  hasMore: boolean;
  isDirty: boolean;
  isLoading: boolean;
  updateItemMedia(imageId: string, updates: Partial<MediaItem>): void;
  nextPage(): Promise<void>;
}

export class MediaFeedAdapter implements PostViewerFeed {
  constructor(private _feed: MediaFeed) {}

  get items(): MediaItem[] {
    return this._feed.media;
  }

  get hasMore(): boolean {
    return this._feed.hasMore;
  }

  get isDirty(): boolean {
    return this._feed.isDirty;
  }

  get isLoading(): boolean {
    return this._feed.isLoading;
  }

  updateItem(item: MediaItem, updates: Partial<MediaItem>): void {
    this._feed.updateItemMedia(item.id, updates);
  }

  async nextPage(): Promise<void> {
    await this._feed.nextPage();
  }
}
