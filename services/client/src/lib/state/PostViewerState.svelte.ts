import { replaceState } from '$app/navigation';

import { ApiClient } from '@slink/api/Client';
import type { ImageListingItem } from '@slink/api/Response';

import type { AbstractPaginatedFeed } from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';
import { useState } from '@slink/lib/state/core/ContextAwareState';

type PaginatedFeed = AbstractPaginatedFeed<ImageListingItem>;
export type PostViewerSource = 'public' | 'collection';

class PostViewerState {
  private _isOpen: boolean = $state(false);
  private _currentIndex: number = $state(0);
  private _feed: PaginatedFeed | null = $state(null);
  private _standaloneItem: ImageListingItem | null = $state(null);
  private _lastFetchedPostId: string | null = null;
  private _prefetchThreshold: number = 3;
  private _prefetchCount: number = 2;
  private _source: PostViewerSource = $state('public');

  get isOpen(): boolean {
    return this._isOpen;
  }

  get currentIndex(): number {
    return this._currentIndex;
  }

  get items(): ImageListingItem[] {
    return this._feed?.items ?? [];
  }

  get currentItem(): ImageListingItem | null {
    if (this._standaloneItem) return this._standaloneItem;
    return this.items[this._currentIndex] ?? null;
  }

  get isStandaloneMode(): boolean {
    return this._standaloneItem !== null;
  }

  get source(): PostViewerSource {
    return this._source;
  }

  get isCollectionContext(): boolean {
    return this._source === 'collection';
  }

  get hasNext(): boolean {
    if (this._standaloneItem) return false;
    return (
      this._currentIndex < this.items.length - 1 ||
      (this._feed?.hasMore ?? false)
    );
  }

  get hasPrev(): boolean {
    if (this._standaloneItem) return false;
    return this._currentIndex > 0;
  }

  get totalItems(): number {
    return this.items.length;
  }

  setFeed(feed: PaginatedFeed, source: PostViewerSource = 'public'): void {
    if (this._feed === feed) return;
    this._feed = feed;
    this._lastFetchedPostId = null;
    this._standaloneItem = null;
    this._source = source;
  }

  updateCurrentItem(updates: Partial<ImageListingItem>): void {
    if (!this._feed || !this.currentItem) return;
    this._feed.updateItem(this.currentItem, updates);
  }

  open(index: number = 0): void {
    this._standaloneItem = null;
    this._lastFetchedPostId = null;
    this._currentIndex = Math.max(0, Math.min(index, this.items.length - 1));
    this._isOpen = true;
    this.updateUrl();
    this.prefetchAdjacent();
    this.checkAndLoadMore();
  }

  close(): void {
    this._isOpen = false;
    this._standaloneItem = null;
    this._lastFetchedPostId = null;
  }

  goToIndex(index: number): void {
    if (index < 0 || index >= this.items.length) return;
    this._currentIndex = index;
    this.updateUrl();
    this.prefetchAdjacent();
    this.checkAndLoadMore();
  }

  next(): void {
    if (this._currentIndex < this.items.length - 1) {
      this._currentIndex++;
      this.updateUrl();
      this.prefetchAdjacent();
      this.checkAndLoadMore();
    }
  }

  prev(): void {
    if (this._currentIndex > 0) {
      this._currentIndex--;
      this.updateUrl();
      this.prefetchAdjacent();
    }
  }

  private updateUrl(): void {
    const item = this.currentItem;
    if (!item) return;

    const url = new URL(window.location.href);
    url.searchParams.set('post', item.id);
    replaceState(url, {});
  }

  clearUrlParam(): void {
    const url = new URL(window.location.href);
    url.searchParams.delete('post');
    replaceState(url, {});
  }

  openFromUrl(): boolean {
    const url = new URL(window.location.href);
    const postId = url.searchParams.get('post');
    if (!postId) return false;

    const index = this.items.findIndex((item) => item.id === postId);
    if (index !== -1) {
      this.open(index);
      return true;
    }
    return false;
  }

  async openFromUrlAsync(): Promise<boolean> {
    const url = new URL(window.location.href);
    const postId = url.searchParams.get('post');
    const feedHasLoaded = this._feed?.isDirty ?? false;

    if (!postId) return false;

    const index = this.items.findIndex((item) => item.id === postId);

    if (index !== -1) {
      if (this._standaloneItem?.id === postId) {
        this._standaloneItem = null;
        this._lastFetchedPostId = null;
      }
      this.open(index);
      return true;
    }

    if (this._standaloneItem?.id === postId) {
      this._isOpen = true;
      return true;
    }

    if (!feedHasLoaded) return false;

    if (this._lastFetchedPostId === postId) return false;

    this._lastFetchedPostId = postId;

    const response = await ApiClient.image.getPublicImageById(postId);
    if (response && response.id) {
      this._standaloneItem = response;
      this._isOpen = true;
      return true;
    }

    this.clearUrlParam();
    return false;
  }

  private prefetchAdjacent(): void {
    for (let i = 1; i <= this._prefetchCount; i++) {
      const nextIndex = this._currentIndex + i;
      const prevIndex = this._currentIndex - i;

      if (nextIndex < this.items.length) {
        this.prefetchImage(this.items[nextIndex]);
      }
      if (prevIndex >= 0) {
        this.prefetchImage(this.items[prevIndex]);
      }
    }
  }

  private prefetchImage(item: ImageListingItem): void {
    const link = document.createElement('link');
    link.rel = 'preload';
    link.as = 'image';
    link.href = `/image/${item.attributes.fileName}`;

    const existingLink = document.querySelector(`link[href="${link.href}"]`);
    if (!existingLink) {
      document.head.appendChild(link);
    }
  }

  private async checkAndLoadMore(): Promise<void> {
    if (!this._feed) return;

    const remainingItems = this.items.length - this._currentIndex - 1;
    if (
      remainingItems <= this._prefetchThreshold &&
      this._feed.hasMore &&
      !this._feed.isLoading
    ) {
      await this._feed.nextPage();
    }
  }
}

const POST_VIEWER_STATE = Symbol('PostViewerState');

const postViewerState = new PostViewerState();

export const usePostViewerState = (): PostViewerState => {
  return useState<PostViewerState>(POST_VIEWER_STATE, postViewerState);
};
