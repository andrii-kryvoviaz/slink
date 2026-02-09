import { ApiClient } from '@slink/api';

import { browser } from '$app/environment';

import type { ImageListingItem } from '@slink/api/Response';

import { ImageFeedEventType } from '@slink/lib/enum/ImageFeedEventType';
import { MercureService } from '@slink/lib/services/mercure.service';
import { AbstractSearchablePaginatedFeed } from '@slink/lib/state/core/AbstractSearchablePaginatedFeed.svelte';
import type { ExtendedSearchParams } from '@slink/lib/state/core/AbstractSearchablePaginatedFeed.svelte';
import { useState } from '@slink/lib/state/core/ContextAwareState';

import type {
  LoadParams,
  PaginatedResponse,
} from './core/AbstractPaginatedFeed.svelte';

interface ImageFeedEvent {
  event: ImageFeedEventType;
  image?: ImageListingItem;
  imageId?: string;
}

class PublicImagesFeed extends AbstractSearchablePaginatedFeed<ImageListingItem> {
  private static readonly TOPIC = 'public-feed';
  private _unsubscribe: (() => void) | null = null;

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
    const { limit = 12, cursor, searchTerm, searchBy } = params;

    return ApiClient.image.getPublicImages(
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

  public subscribe(): void {
    if (!browser || this._unsubscribe) return;

    MercureService.getInstance()
      .subscribe(PublicImagesFeed.TOPIC, (data) =>
        this.handleFeedEvent(data as ImageFeedEvent),
      )
      .then((unsubscribe) => {
        this._unsubscribe = unsubscribe;
      });
  }

  public unsubscribe(): void {
    this._unsubscribe?.();
    this._unsubscribe = null;
  }

  private handleFeedEvent(event: ImageFeedEvent): void {
    if (this.isSearching) return;

    switch (event.event) {
      case ImageFeedEventType.Added:
        this.handleImageAdded(event.image);
        break;
      case ImageFeedEventType.Updated:
        this.handleImageUpdated(event.image);
        break;
      case ImageFeedEventType.Removed:
        this.handleImageRemoved(event.imageId);
        break;
    }
  }

  private isValidImage(image: unknown): image is ImageListingItem {
    if (!image || typeof image !== 'object') return false;
    const img = image as Record<string, unknown>;
    if (typeof img.id !== 'string') return false;
    if (!img.attributes || typeof img.attributes !== 'object') return false;
    const attrs = img.attributes as Record<string, unknown>;
    return typeof attrs.createdAt === 'object';
  }

  private async fetchBookmarkStatus(imageId: string): Promise<boolean> {
    try {
      const response = await ApiClient.bookmark.getBookmarkStatus(imageId);
      return response.isBookmarked;
    } catch {
      return false;
    }
  }

  private async handleImageAdded(image?: ImageListingItem): Promise<void> {
    if (!this.isValidImage(image) || this._hasItem(image)) return;
    const isBookmarked = await this.fetchBookmarkStatus(image.id);
    if (this._hasItem(image)) return;
    this.addItem({ ...image, isBookmarked });
  }

  private async handleImageUpdated(image?: ImageListingItem): Promise<void> {
    if (!this.isValidImage(image)) return;
    const existing = this._items.find((item) => item.id === image.id);
    if (existing) {
      this.replaceItem({ ...image, isBookmarked: existing.isBookmarked });
    } else {
      const isBookmarked = await this.fetchBookmarkStatus(image.id);
      this.replaceItem({ ...image, isBookmarked });
    }
  }

  private handleImageRemoved(imageId?: string): void {
    if (!imageId) return;
    this.removeItem(imageId);
  }
}

const PUBLIC_IMAGES_FEED = Symbol('PublicImagesFeed');

const publicImagesFeed = new PublicImagesFeed();

export const usePublicImagesFeed = (): PublicImagesFeed => {
  return useState<PublicImagesFeed>(PUBLIC_IMAGES_FEED, publicImagesFeed);
};
