import { afterEach, describe, expect, it, vi } from 'vitest';

import type { NotificationItem } from '@slink/api/Response';

import {
  NotificationDayBuckets,
  type NotificationGroup,
  NotificationGrouping,
} from '@slink/utils/notification';

const now = new Date(2026, 8, 18, 12, 0);

const at = (day: number, hours: number, minutes = 0, seconds = 0): number =>
  new Date(2026, 8, day, hours, minutes, seconds).getTime() / 1000;

let nextId = 0;

const item = (imageId: string, timestamp: number): NotificationItem => ({
  id: `n-${++nextId}`,
  type: 'added_to_bookmarks',
  message: '',
  reference: { id: imageId, fileName: `${imageId}.png` },
  relatedComment: null,
  actor: { id: 'u-a', displayName: 'A' },
  isRead: false,
  createdAt: { formattedDate: '', timestamp },
});

const group = (...timestamps: number[]): NotificationGroup => {
  const imageId = `img-${++nextId}`;
  const [single] = NotificationGrouping.group(
    timestamps.map((timestamp) => item(imageId, timestamp)),
  );
  return single;
};

describe('NotificationDayBuckets.fromGroups', () => {
  afterEach(() => {
    vi.useRealTimers();
  });

  it('buckets today, yesterday and earlier groups newest day first', () => {
    const today = group(at(18, 9));
    const yesterday = group(at(17, 18));
    const earlier = group(at(13, 10));

    const buckets = NotificationDayBuckets.fromGroups(
      [today, yesterday, earlier],
      now,
    );

    expect(buckets.map((bucket) => bucket.kind)).toEqual([
      'today',
      'yesterday',
      'earlier',
    ]);
    expect(buckets.map((bucket) => bucket.key)).toEqual([
      '2026-09-18',
      '2026-09-17',
      '2026-09-13',
    ]);
    expect(buckets.map((bucket) => bucket.groups)).toEqual([
      [today],
      [yesterday],
      [earlier],
    ]);
  });

  it('sets each bucket day to local midnight of its key', () => {
    const [bucket] = NotificationDayBuckets.fromGroups(
      [group(at(17, 18))],
      now,
    );

    expect(bucket.day.getHours()).toBe(0);
    expect(bucket.day.getMinutes()).toBe(0);
    expect(bucket.day.getDate()).toBe(17);
  });

  it('shares one bucket between groups on the same day in input order', () => {
    const later = group(at(18, 11));
    const earlier = group(at(18, 8));

    const buckets = NotificationDayBuckets.fromGroups([later, earlier], now);

    expect(buckets).toHaveLength(1);
    expect(buckets[0].groups).toEqual([later, earlier]);
  });

  it('orders buckets by first appearance', () => {
    const today = group(at(18, 11));
    const yesterday = group(at(17, 9));
    const todayOlder = group(at(18, 7));

    const buckets = NotificationDayBuckets.fromGroups(
      [today, yesterday, todayOlder],
      now,
    );

    expect(buckets.map((bucket) => bucket.key)).toEqual([
      '2026-09-18',
      '2026-09-17',
    ]);
    expect(buckets[0].groups).toEqual([today, todayOlder]);
  });

  it('places a group spanning a week in the day of its latest item', () => {
    const buckets = NotificationDayBuckets.fromGroups(
      [group(at(18, 10), at(11, 10))],
      now,
    );

    expect(buckets).toHaveLength(1);
    expect(buckets[0].kind).toBe('today');
  });

  it('classifies moments at the midnight boundaries', () => {
    const buckets = NotificationDayBuckets.fromGroups(
      [group(at(18, 0)), group(at(17, 23, 59, 59)), group(at(16, 23, 59, 59))],
      now,
    );

    expect(buckets.map((bucket) => bucket.kind)).toEqual([
      'today',
      'yesterday',
      'earlier',
    ]);
  });

  it('keys late-night moments by the local calendar day', () => {
    const lateNow = new Date(2026, 8, 18, 23, 45);

    const [bucket] = NotificationDayBuckets.fromGroups(
      [group(at(18, 23, 30))],
      lateNow,
    );

    expect(bucket.key).toBe('2026-09-18');
    expect(bucket.kind).toBe('today');
  });

  it('defaults now to the current time', () => {
    vi.useFakeTimers().setSystemTime(now);

    const [bucket] = NotificationDayBuckets.fromGroups([
      group(Date.now() / 1000),
    ]);

    expect(bucket.kind).toBe('today');
  });

  it('returns no buckets for no groups', () => {
    expect(NotificationDayBuckets.fromGroups([], now)).toEqual([]);
  });

  it('leaves the input untouched', () => {
    const groups = [group(at(18, 9)), group(at(17, 9))];
    const snapshot = structuredClone(groups);

    NotificationDayBuckets.fromGroups(groups, now);

    expect(groups).toEqual(snapshot);
  });
});
