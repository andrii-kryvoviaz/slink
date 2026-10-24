import { ApiClient } from '@slink/api';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import type { ImageListingItem } from '@slink/api/Response';

import { ImageFeedEventType } from '@slink/lib/enum/ImageFeedEventType';
import { usePublicImagesFeed } from '@slink/lib/state/PublicImagesFeed.svelte';

const subscription = vi.hoisted(() => ({
  onMessage: (_data: unknown): void | Promise<void> => {},
}));
const viewer = vi.hoisted<{ user: { id: string } | null }>(() => ({
  user: { id: 'viewer' },
}));

vi.mock('$app/env', () => ({ browser: true }));
vi.mock('$app/state', () => ({ page: { data: viewer } }));
vi.mock('@slink/lib/state/core/ContextAwareState', () => ({
  useState: (_key: symbol, state: unknown) => state,
}));
vi.mock('@slink/lib/services/mercure.service', () => ({
  MercureService: {
    getInstance: () => ({
      subscribe: async (
        _topic: string,
        onMessage: typeof subscription.onMessage,
      ) => {
        subscription.onMessage = onMessage;
        return () => {};
      },
    }),
  },
}));
vi.mock('@slink/api', () => ({
  ApiClient: {
    image: { getPublicImages: vi.fn() },
    bookmark: { getBookmarkStatus: vi.fn() },
  },
}));

const getPublicImages = vi.mocked(ApiClient.image.getPublicImages);
const getBookmarkStatus = vi.mocked(ApiClient.bookmark.getBookmarkStatus);
const feed = usePublicImagesFeed();

const image = (
  id: string,
  description = id,
  timestamp = 1,
): ImageListingItem => ({
  id,
  url: `/image/${id}.png`,
  owner: { id: 'owner', email: '', displayName: 'Owner' },
  attributes: {
    fileName: `${id}.png`,
    description,
    isPublic: true,
    createdAt: { formattedDate: '', timestamp },
    views: 0,
  },
  metadata: { size: 1, mimeType: 'image/png', width: 1, height: 1 },
  license: {
    id: 'none',
    title: '',
    name: '',
    description: '',
    url: null,
    isCreativeCommons: false,
  },
  bookmarkCount: 0,
});

const deliver = async (event: ImageFeedEventType, item: ImageListingItem) => {
  subscription.onMessage({ event, image: item });
  await Promise.resolve();
  await Promise.resolve();
};

describe('PublicImagesFeed delivery', () => {
  beforeEach(async () => {
    feed.unsubscribe();
    feed.resetSearch();
    vi.resetAllMocks();
    viewer.user = { id: 'viewer' };
    getBookmarkStatus.mockResolvedValue({ isBookmarked: false });
    getPublicImages.mockResolvedValue({
      data: [],
      meta: { page: 1, size: 12, total: 0 },
    });
    feed.subscribe();
    await Promise.resolve();
  });

  it('shows public images to guests without requesting private bookmark status', async () => {
    viewer.user = null;

    await deliver(ImageFeedEventType.Added, image('image-1'));

    expect(feed.items).toEqual([{ ...image('image-1'), isBookmarked: false }]);
    expect(getBookmarkStatus).not.toHaveBeenCalled();
  });

  it('refreshes an existing image on repeated added delivery without losing its bookmark', async () => {
    feed.addItem({ ...image('image-1', 'Old'), isBookmarked: true });

    await deliver(ImageFeedEventType.Added, image('image-1', 'Current'));
    await deliver(ImageFeedEventType.Added, image('image-1', 'Current'));

    expect(feed.items).toEqual([
      { ...image('image-1', 'Current'), isBookmarked: true },
    ]);
    expect(getBookmarkStatus).not.toHaveBeenCalled();
    expect(feed.meta.total).toBe(0);
  });

  it('inserts an unknown image on updated delivery and keeps one item after retries', async () => {
    getBookmarkStatus.mockResolvedValue({ isBookmarked: true });

    await deliver(ImageFeedEventType.Updated, image('image-1'));
    await deliver(ImageFeedEventType.Updated, image('image-1'));

    expect(feed.items).toEqual([{ ...image('image-1'), isBookmarked: true }]);
    expect(getBookmarkStatus).toHaveBeenCalledTimes(1);
  });

  it('leaves an older unseen bookmark update to pagination instead of moving it ahead of newer images', async () => {
    getPublicImages.mockResolvedValueOnce({
      data: [image('newest', 'Newest', 30), image('visible', 'Visible', 20)],
      meta: { page: 1, size: 2, total: 4, nextCursor: 'next' },
    });
    await feed.load();
    const older = { ...image('old', 'Old', 5), bookmarkCount: 2 };

    await deliver(ImageFeedEventType.Updated, older);
    await deliver(ImageFeedEventType.Updated, older);

    expect(feed.items.map((item) => item.id)).toEqual(['newest', 'visible']);
    getPublicImages.mockResolvedValueOnce({
      data: [image('middle', 'Middle', 10), older],
      meta: { page: 1, size: 2, total: 4 },
    });
    await feed.nextPage();
    expect(feed.items.map((item) => item.id)).toEqual([
      'newest',
      'visible',
      'middle',
      'old',
    ]);
    expect(feed.get('old')?.bookmarkCount).toBe(2);
  });

  it('inserts missing recent updates in chronological order and keeps that order on retries and pagination', async () => {
    getPublicImages.mockResolvedValueOnce({
      data: [image('newest', 'Newest', 30), image('visible', 'Visible', 20)],
      meta: { page: 1, size: 2, total: 4, nextCursor: 'next' },
    });
    await feed.load();

    await deliver(ImageFeedEventType.Updated, image('missed', 'Missed', 25));
    await deliver(ImageFeedEventType.Updated, image('missed', 'Current', 25));
    expect(feed.items.map((item) => item.id)).toEqual([
      'newest',
      'missed',
      'visible',
    ]);
    expect(feed.get('missed')?.attributes.description).toBe('Current');

    getPublicImages.mockResolvedValueOnce({
      data: [image('old', 'Old', 10)],
      meta: { page: 1, size: 2, total: 4 },
    });
    await feed.nextPage();
    expect(feed.items.map((item) => item.id)).toEqual([
      'newest',
      'missed',
      'visible',
      'old',
    ]);
  });

  it('uses descending IDs to place missing updates with equal creation timestamps', async () => {
    getPublicImages.mockResolvedValueOnce({
      data: [image('d'), image('b')],
      meta: { page: 1, size: 2, total: 4, nextCursor: 'next' },
    });
    await feed.load();

    await deliver(ImageFeedEventType.Updated, image('c'));
    await deliver(ImageFeedEventType.Updated, image('a'));

    expect(feed.items.map((item) => item.id)).toEqual(['d', 'c', 'b']);
  });

  it('appends a missing older update when all pages are loaded', async () => {
    getPublicImages.mockResolvedValueOnce({
      data: [image('newest', 'Newest', 30), image('visible', 'Visible', 20)],
      meta: { page: 1, size: 2, total: 2 },
    });
    await feed.load();

    await deliver(ImageFeedEventType.Updated, image('old', 'Old', 10));
    await deliver(ImageFeedEventType.Updated, image('old', 'Current', 10));

    expect(feed.items.map((item) => item.id)).toEqual([
      'newest',
      'visible',
      'old',
    ]);
    expect(feed.get('old')?.attributes.description).toBe('Current');
  });

  it('keeps the existing prepend behavior for a newly public older image', async () => {
    getPublicImages.mockResolvedValueOnce({
      data: [image('newest', 'Newest', 30), image('visible', 'Visible', 20)],
      meta: { page: 1, size: 2, total: 3, nextCursor: 'next' },
    });
    await feed.load();

    await deliver(ImageFeedEventType.Added, image('old', 'Newly public', 10));
    await deliver(ImageFeedEventType.Added, image('old', 'Newly public', 10));

    expect(feed.items.map((item) => item.id)).toEqual([
      'old',
      'newest',
      'visible',
    ]);
  });

  it.each([ImageFeedEventType.Added, ImageFeedEventType.Updated])(
    'does not resurrect a removed image when a delayed %s bookmark lookup finishes',
    async (event) => {
      const pending = Promise.withResolvers<{ isBookmarked: boolean }>();
      getBookmarkStatus.mockReturnValueOnce(pending.promise);
      await deliver(event, image('image-1'));

      subscription.onMessage({
        event: ImageFeedEventType.Removed,
        imageId: 'image-1',
      });
      pending.resolve({ isBookmarked: true });
      await pending.promise;
      await Promise.resolve();

      expect(feed.items).toEqual([]);
    },
  );

  it('keeps a newer update and local bookmark when an earlier added lookup finishes', async () => {
    const pending = Promise.withResolvers<{ isBookmarked: boolean }>();
    getBookmarkStatus.mockReturnValueOnce(pending.promise);
    await deliver(ImageFeedEventType.Added, image('image-1', 'Old'));
    feed.addItem({ ...image('image-1'), isBookmarked: true });

    await deliver(ImageFeedEventType.Updated, image('image-1', 'Current'));
    pending.resolve({ isBookmarked: false });
    await pending.promise;
    await Promise.resolve();

    expect(feed.items).toEqual([
      { ...image('image-1', 'Current'), isBookmarked: true },
    ]);
  });

  it('keeps an image loaded during a pending bookmark lookup without inserting it twice', async () => {
    const pending = Promise.withResolvers<{ isBookmarked: boolean }>();
    getBookmarkStatus.mockReturnValueOnce(pending.promise);
    await deliver(ImageFeedEventType.Added, image('image-1', 'Old'));
    getPublicImages.mockResolvedValueOnce({
      data: [{ ...image('image-1', 'Current'), isBookmarked: true }],
      meta: { page: 1, size: 12, total: 1 },
    });
    await feed.load();

    pending.resolve({ isBookmarked: false });
    await pending.promise;
    await Promise.resolve();

    expect(feed.items).toEqual([
      { ...image('image-1', 'Current'), isBookmarked: true },
    ]);
    expect(feed.meta.total).toBe(1);
  });

  it('discards a pending image after leaving the feed', async () => {
    const pending = Promise.withResolvers<{ isBookmarked: boolean }>();
    getBookmarkStatus.mockReturnValueOnce(pending.promise);
    await deliver(ImageFeedEventType.Added, image('image-1'));

    feed.unsubscribe();
    pending.resolve({ isBookmarked: false });
    await pending.promise;
    await Promise.resolve();

    expect(feed.items).toEqual([]);
  });

  it('does not inject a pending public image into search results', async () => {
    const pending = Promise.withResolvers<{ isBookmarked: boolean }>();
    getBookmarkStatus.mockReturnValueOnce(pending.promise);
    await deliver(ImageFeedEventType.Added, image('image-1'));

    feed.setSearch('unrelated');
    pending.resolve({ isBookmarked: false });
    await pending.promise;
    await Promise.resolve();

    expect(feed.items).toEqual([]);
  });

  it('does not revive an image removed during search after search ends', async () => {
    const pending = Promise.withResolvers<{ isBookmarked: boolean }>();
    getBookmarkStatus.mockReturnValueOnce(pending.promise);
    await deliver(ImageFeedEventType.Added, image('image-1'));

    feed.setSearch('unrelated');
    await subscription.onMessage({
      event: ImageFeedEventType.Removed,
      imageId: 'image-1',
    });
    feed.resetSearch();
    pending.resolve({ isBookmarked: false });
    await pending.promise;
    await Promise.resolve();

    expect(feed.items).toEqual([]);
  });

  it.each([true, false])(
    'keeps the latest delivered state when overlapping lookups finish newest first: %s',
    async (newestFirst) => {
      const older = Promise.withResolvers<{ isBookmarked: boolean }>();
      const newer = Promise.withResolvers<{ isBookmarked: boolean }>();
      getBookmarkStatus
        .mockReturnValueOnce(older.promise)
        .mockReturnValueOnce(newer.promise);
      await deliver(ImageFeedEventType.Added, image('image-1', 'Old'));
      await deliver(ImageFeedEventType.Updated, image('image-1', 'Current'));

      const requests = [
        { deferred: older, isBookmarked: false },
        { deferred: newer, isBookmarked: true },
      ];
      if (newestFirst) requests.reverse();
      for (const { deferred, isBookmarked } of requests) {
        deferred.resolve({ isBookmarked });
        await deferred.promise;
        await Promise.resolve();
      }

      expect(feed.items).toEqual([
        { ...image('image-1', 'Current'), isBookmarked: true },
      ]);
    },
  );

  it('removes an image once and requests only one backfill after repeated removal', async () => {
    getPublicImages.mockResolvedValueOnce({
      data: [image('image-1'), image('image-2')],
      meta: { page: 1, size: 2, total: 3, nextCursor: 'next' },
    });
    await feed.load();
    const pending =
      Promise.withResolvers<
        Awaited<ReturnType<typeof ApiClient.image.getPublicImages>>
      >();
    getPublicImages.mockReturnValueOnce(pending.promise);

    const removal = { event: ImageFeedEventType.Removed, imageId: 'image-1' };
    const removing = subscription.onMessage(removal);
    subscription.onMessage(removal);
    expect(feed.items.map((item) => item.id)).toEqual(['image-2']);
    expect(getPublicImages).toHaveBeenCalledTimes(2);

    pending.resolve({
      data: [image('image-3')],
      meta: { page: 1, size: 1, total: 2 },
    });
    await removing;

    expect(feed.items.map((item) => item.id)).toEqual(['image-2', 'image-3']);
    expect(feed.meta.total).toBe(2);
  });
});
