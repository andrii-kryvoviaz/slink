import { ApiClient } from '@slink/api';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import type { NotificationItem } from '@slink/api/Response';

import { useNotificationFeed } from '@slink/lib/state/NotificationFeed.svelte';

import { NotificationDayBuckets } from '@slink/utils/notification';

vi.mock('@slink/lib/state/core/ContextAwareState', () => ({
  useState: (_key: symbol, state: unknown) => state,
}));
vi.mock('@slink/api', () => ({
  ApiClient: {
    notification: {
      markAsRead: vi.fn(async () => undefined),
      markAllAsRead: vi.fn(async () => undefined),
      getUnreadCount: vi.fn(async () => ({ count: 7 })),
    },
  },
}));

const markAsRead = vi.mocked(ApiClient.notification.markAsRead);
const markAllAsRead = vi.mocked(ApiClient.notification.markAllAsRead);
const getUnreadCount = vi.mocked(ApiClient.notification.getUnreadCount);

const item = (
  id: string,
  imageId: string,
  timestamp: number,
  isRead = false,
): NotificationItem => ({
  id,
  type: 'comment_reply',
  message: '',
  reference: { id: imageId, fileName: `${imageId}.png` },
  relatedComment: null,
  actor: { id: `u-${id}`, displayName: id },
  isRead,
  createdAt: { formattedDate: '', timestamp },
});

const seedFeed = async (count = 7) => {
  getUnreadCount.mockResolvedValueOnce({ count });
  const feed = useNotificationFeed();

  [
    item('other-1', 'img-2', 50),
    item('read', 'img-1', 100, true),
    item('unread-1', 'img-1', 200),
    item('unread-2', 'img-1', 300),
    item('unread-3', 'img-1', 400),
  ].forEach((entry) => feed.addItem(entry));

  await feed.loadUnreadCount();
  return feed;
};

const groupOf = (feed: Awaited<ReturnType<typeof seedFeed>>, imageId: string) =>
  feed.groupedItems.find((group) => group.reference.id === imageId)!;

const seedMarkAsReadFeed = async (unreadCount = 3) => {
  getUnreadCount.mockResolvedValueOnce({ count: unreadCount });
  const feed = useNotificationFeed();

  [
    item('n-unread', 'img-1', 100),
    item('n-read', 'img-2', 200, true),
    item('n-other', 'img-3', 300),
  ].forEach((entry) => feed.addItem(entry));

  await feed.loadUnreadCount();
  return feed;
};

describe('NotificationFeed.markGroupAsRead', () => {
  beforeEach(() => {
    markAsRead.mockReset();
    markAsRead.mockResolvedValue(undefined);
  });

  it('marks only the unread items of the group read', async () => {
    const feed = await seedFeed();

    await feed.markGroupAsRead(groupOf(feed, 'img-1'));

    expect(markAsRead).toHaveBeenCalledTimes(3);
    expect(markAsRead.mock.calls.map(([id]) => id).toSorted()).toEqual([
      'unread-1',
      'unread-2',
      'unread-3',
    ]);
    expect(groupOf(feed, 'img-1').isRead).toBe(true);
    expect(groupOf(feed, 'img-1').unreadCount).toBe(0);
  });

  it('lowers the unread counter by the number of items marked', async () => {
    const feed = await seedFeed(7);

    await feed.markGroupAsRead(groupOf(feed, 'img-1'));

    expect(feed.unreadCount).toBe(4);
  });

  it('floors the unread counter at zero', async () => {
    const feed = await seedFeed(1);

    await feed.markGroupAsRead(groupOf(feed, 'img-1'));

    expect(feed.unreadCount).toBe(0);
  });

  it('sends all requests before any resolves', async () => {
    const feed = await seedFeed();
    const pending: Array<() => void> = [];
    markAsRead.mockImplementation(
      () => new Promise<void>((resolve) => pending.push(resolve)),
    );

    const marking = feed.markGroupAsRead(groupOf(feed, 'img-1'));

    expect(markAsRead).toHaveBeenCalledTimes(3);
    pending.forEach((resolve) => resolve());
    await marking;
  });

  it('makes no request for a fully read group', async () => {
    const feed = await seedFeed(7);
    await feed.markGroupAsRead(groupOf(feed, 'img-1'));
    markAsRead.mockClear();

    await feed.markGroupAsRead(groupOf(feed, 'img-1'));

    expect(markAsRead).not.toHaveBeenCalled();
    expect(feed.unreadCount).toBe(4);
  });

  it('patches the fulfilled items and leaves the rejected one unread', async () => {
    const feed = await seedFeed(7);
    markAsRead.mockImplementation(async (id: string) => {
      if (id === 'unread-2') {
        throw new Error('offline');
      }
    });

    await expect(
      feed.markGroupAsRead(groupOf(feed, 'img-1')),
    ).resolves.toBeUndefined();

    const group = groupOf(feed, 'img-1');
    expect(
      group.items
        .filter((entry) => entry.isRead)
        .map((entry) => entry.id)
        .toSorted(),
    ).toEqual(['read', 'unread-1', 'unread-3']);
    expect(group.items.find((entry) => entry.id === 'unread-2')?.isRead).toBe(
      false,
    );
    expect(feed.unreadCount).toBe(5);
  });

  it('lowers the counter by the real flips when two group marks race', async () => {
    const feed = await seedFeed(7);
    const group = groupOf(feed, 'img-1');
    const pending: Array<() => void> = [];
    markAsRead.mockImplementation(
      () => new Promise<void>((resolve) => pending.push(resolve)),
    );

    const first = feed.markGroupAsRead(group);
    const second = feed.markGroupAsRead(group);

    pending.forEach((resolve) => resolve());
    await Promise.all([first, second]);

    expect(feed.unreadCount).toBe(4);
  });

  it('lowers the counter by the real flips when a single mark races the group mark', async () => {
    const feed = await seedFeed(7);
    const group = groupOf(feed, 'img-1');
    const pending: Array<() => void> = [];
    markAsRead.mockImplementation(
      () => new Promise<void>((resolve) => pending.push(resolve)),
    );

    const single = feed.markAsRead('unread-1');
    const grouped = feed.markGroupAsRead(group);

    pending.forEach((resolve) => resolve());
    await Promise.all([single, grouped]);

    expect(feed.unreadCount).toBe(4);
  });

  it('leaves other groups unread', async () => {
    const feed = await seedFeed();

    await feed.markGroupAsRead(groupOf(feed, 'img-1'));

    expect(groupOf(feed, 'img-2').isRead).toBe(false);
  });
});

describe('NotificationFeed.dayBuckets', () => {
  it('re-derives read flags without reshaping the buckets', async () => {
    const feed = await seedFeed();
    const before = feed.dayBuckets;

    await feed.markGroupAsRead(groupOf(feed, 'img-1'));
    const after = feed.dayBuckets;

    expect(after.map((bucket) => bucket.key)).toEqual(
      before.map((bucket) => bucket.key),
    );
    expect(after.flatMap((bucket) => bucket.groups.map((g) => g.key))).toEqual(
      before.flatMap((bucket) => bucket.groups.map((g) => g.key)),
    );
    expect(before[0].groups[0].isRead).toBe(false);
    expect(after[0].groups[0].isRead).toBe(true);
  });

  it('folds the grouped items into day buckets', async () => {
    const feed = await seedFeed();

    expect(feed.dayBuckets).toEqual(
      NotificationDayBuckets.fromGroups(feed.groupedItems),
    );
  });
});

describe('NotificationFeed.markAsRead', () => {
  beforeEach(() => {
    markAsRead.mockReset();
    markAsRead.mockResolvedValue(undefined);
  });

  it('flips the item read locally and drops the counter by one', async () => {
    const feed = await seedMarkAsReadFeed();

    await feed.markAsRead('n-unread');

    expect(markAsRead).toHaveBeenCalledTimes(1);
    expect(markAsRead).toHaveBeenCalledWith('n-unread');
    expect(feed.items.find((entry) => entry.id === 'n-unread')?.isRead).toBe(
      true,
    );
    expect(feed.unreadCount).toBe(2);
  });

  it('patches only isRead and leaves the rest of the feed untouched', async () => {
    const feed = await seedMarkAsReadFeed();
    const before = feed.items;

    await feed.markAsRead('n-unread');

    const patched = feed.items.find((entry) => entry.id === 'n-unread')!;
    expect(patched).toEqual({
      ...before.find((entry) => entry.id === 'n-unread'),
      isRead: true,
    });
    expect(feed.items.map((entry) => entry.id)).toEqual(
      before.map((entry) => entry.id),
    );
    expect(feed.items.find((entry) => entry.id === 'n-other')?.isRead).toBe(
      false,
    );
  });

  it('re-derives groupedItems and dayBuckets for the marked item', async () => {
    const feed = await seedMarkAsReadFeed();

    await feed.markAsRead('n-unread');

    const group = groupOf(feed, 'img-1');
    expect(group.isRead).toBe(true);
    expect(group.unreadCount).toBe(0);

    const bucketGroup = feed.dayBuckets
      .flatMap((bucket) => bucket.groups)
      .find((entry) => entry.reference.id === 'img-1')!;
    expect(bucketGroup.isRead).toBe(true);
  });

  it('still sends the request for an already read item and leaves it unchanged', async () => {
    const feed = await seedMarkAsReadFeed();

    await feed.markAsRead('n-read');

    expect(markAsRead).toHaveBeenCalledTimes(1);
    expect(markAsRead).toHaveBeenCalledWith('n-read');
    expect(feed.items.find((entry) => entry.id === 'n-read')?.isRead).toBe(
      true,
    );
    expect(feed.unreadCount).toBe(3);
  });

  it('resolves for an unknown id without touching state', async () => {
    const feed = await seedMarkAsReadFeed();
    const before = feed.items;

    await feed.markAsRead('missing');

    expect(markAsRead).toHaveBeenCalledTimes(1);
    expect(markAsRead).toHaveBeenCalledWith('missing');
    expect(feed.unreadCount).toBe(3);
    expect(feed.items).toEqual(before);
  });

  it('leaves the item and counter untouched when the request rejects', async () => {
    const feed = await seedMarkAsReadFeed();
    markAsRead.mockRejectedValueOnce(new Error('boom'));

    await expect(feed.markAsRead('n-unread')).resolves.toBeUndefined();

    expect(feed.items.find((entry) => entry.id === 'n-unread')?.isRead).toBe(
      false,
    );
    expect(feed.unreadCount).toBe(3);
  });

  it('does not flip optimistically before the request resolves', async () => {
    const feed = await seedMarkAsReadFeed();
    let resolve!: () => void;
    markAsRead.mockImplementationOnce(
      () =>
        new Promise<void>((res) => {
          resolve = res;
        }),
    );

    const marking = feed.markAsRead('n-unread');

    expect(feed.items.find((entry) => entry.id === 'n-unread')?.isRead).toBe(
      false,
    );
    expect(feed.unreadCount).toBe(3);

    resolve();
    await marking;

    expect(feed.items.find((entry) => entry.id === 'n-unread')?.isRead).toBe(
      true,
    );
    expect(feed.unreadCount).toBe(2);
  });

  it('floors the unread counter at zero', async () => {
    const feed = await seedMarkAsReadFeed(0);

    await feed.markAsRead('n-unread');

    expect(feed.unreadCount).toBe(0);
  });

  it('decrements once when two concurrent calls both resolve', async () => {
    const feed = await seedMarkAsReadFeed();

    await Promise.all([
      feed.markAsRead('n-unread'),
      feed.markAsRead('n-unread'),
    ]);

    expect(markAsRead).toHaveBeenCalledTimes(2);
    expect(feed.items.find((entry) => entry.id === 'n-unread')?.isRead).toBe(
      true,
    );
    expect(feed.unreadCount).toBe(2);
  });
});

describe('NotificationFeed.markAllAsRead', () => {
  beforeEach(() => {
    markAllAsRead.mockReset();
    markAllAsRead.mockResolvedValue(undefined);
  });

  it('marks every item read and zeroes the counter', async () => {
    const feed = await seedMarkAsReadFeed();

    await feed.markAllAsRead();

    expect(feed.items.every((entry) => entry.isRead)).toBe(true);
    expect(feed.unreadCount).toBe(0);
  });

  it('leaves items and the counter untouched when the request rejects', async () => {
    const feed = await seedMarkAsReadFeed();
    markAllAsRead.mockRejectedValueOnce(new Error('offline'));
    const before = feed.items;

    await expect(feed.markAllAsRead()).resolves.toBeUndefined();

    expect(feed.items).toEqual(before);
    expect(feed.unreadCount).toBe(3);
  });
});
