import { ApiClient } from '@slink/api';

import { browser } from '$app/env';
import { page } from '$app/state';

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
  private _pendingImages = new Map<string, symbol>();

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
    this._pendingImages.clear();
    this._unsubscribe?.();
    this._unsubscribe = null;
  }

  private async handleFeedEvent(event: ImageFeedEvent): Promise<void> {
    if (this.isSearching) {
      this._pendingImages.clear();
      return;
    }

    switch (event.event) {
      case ImageFeedEventType.Added:
      case ImageFeedEventType.Updated:
        return this.handleImageUpsert(event.event, event.image);
      case ImageFeedEventType.Removed:
        return this.handleImageRemoved(event.imageId);
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
    if (!page.data.user) return false;

    try {
      const response = await ApiClient.bookmark.getBookmarkStatus(imageId);
      return response.isBookmarked;
    } catch {
      return false;
    }
  }

  private async handleImageUpsert(
    event: ImageFeedEventType,
    image?: ImageListingItem,
  ): Promise<void> {
    if (!this.isValidImage(image)) return;
    this._pendingImages.delete(image.id);
    const existing = this.get(image.id);
    if (existing) {
      this.replaceItem({ ...image, isBookmarked: existing.isBookmarked });
      return;
    }

    const pending = Symbol();
    this._pendingImages.set(image.id, pending);
    const isBookmarked = await this.fetchBookmarkStatus(image.id);
    if (this._pendingImages.get(image.id) !== pending) return;
    this._pendingImages.delete(image.id);
    if (this.isSearching || this.has(image.id)) return;
    if (event === ImageFeedEventType.Added) {
      this.addItem({ ...image, isBookmarked });
      return;
    }
    this.insertUpdatedImage({ ...image, isBookmarked });
  }

  private insertUpdatedImage(image: ImageListingItem): void {
    const items = this.items;
    const timestamp = image.attributes.createdAt.timestamp;
    let index = items.findIndex(
      (item) =>
        item.attributes.createdAt.timestamp < timestamp ||
        (item.attributes.createdAt.timestamp === timestamp &&
          item.id < image.id),
    );
    if (index === -1 && this.hasMore && items.length > 0) return;
    if (index === -1) index = items.length;
    this._items = items.toSpliced(index, 0, image);
  }

  private async handleImageRemoved(imageId?: string): Promise<void> {
    if (!imageId) return;
    this._pendingImages.delete(imageId);
    await this.removeItems([imageId]);
  }
}

const PUBLIC_IMAGES_FEED = Symbol('PublicImagesFeed');

const publicImagesFeed = new PublicImagesFeed();

export const usePublicImagesFeed = (): PublicImagesFeed => {
  return useState<PublicImagesFeed>(PUBLIC_IMAGES_FEED, publicImagesFeed);
};
