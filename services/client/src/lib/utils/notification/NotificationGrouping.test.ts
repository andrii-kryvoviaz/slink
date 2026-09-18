import { describe, expect, it } from 'vitest';

import type {
  NotificationActor,
  NotificationItem,
  NotificationRelatedComment,
} from '@slink/api/Response';

import { NotificationGrouping } from '@slink/utils/notification';

const A: NotificationActor = { id: 'u-a', displayName: 'A' };
const B: NotificationActor = { id: 'u-b', displayName: 'B' };
const C: NotificationActor = { id: 'u-c', displayName: 'C' };

type ItemOverrides = Partial<Omit<NotificationItem, 'createdAt'>> & {
  timestamp?: number;
};

let nextId = 0;

const item = (overrides: ItemOverrides = {}): NotificationItem => {
  const { timestamp = 1_700_000_000, ...rest } = overrides;

  return {
    id: `n-${++nextId}`,
    type: 'added_to_bookmarks',
    message: '',
    reference: { id: 'img-1', fileName: 'a.png' },
    relatedComment: null,
    actor: A,
    isRead: false,
    createdAt: { formattedDate: '', timestamp },
    ...rest,
  };
};

const comment = (id: string): NotificationRelatedComment => ({
  id,
  content: `comment ${id}`,
  isDeleted: false,
});

describe('NotificationGrouping.group', () => {
  it('collapses the same action on one image by several actors into one group', () => {
    const groups = NotificationGrouping.group([
      item({ actor: A, timestamp: 300 }),
      item({ actor: B, timestamp: 200 }),
      item({ actor: A, timestamp: 100 }),
    ]);

    expect(groups).toHaveLength(1);
    expect(groups[0].key).toBe('added_to_bookmarks:img-1');
    expect(groups[0].actors).toEqual([A, B]);
    expect(groups[0].items).toHaveLength(3);
    expect(groups[0].visitorCount).toBe(0);
  });

  it('sorts items newest first regardless of input order', () => {
    const groups = NotificationGrouping.group([
      item({ actor: B, timestamp: 200 }),
      item({ actor: A, timestamp: 100 }),
      item({ actor: A, timestamp: 300 }),
    ]);

    expect(groups[0].actors).toEqual([A, B]);
    expect(groups[0].items.map((i) => i.createdAt.timestamp)).toEqual([
      300, 200, 100,
    ]);
  });

  it('orders actors by their newest item, not first appearance', () => {
    const groups = NotificationGrouping.group([
      item({ actor: A, timestamp: 100 }),
      item({ actor: B, timestamp: 200 }),
      item({ actor: C, timestamp: 300 }),
      item({ actor: A, timestamp: 400 }),
    ]);

    expect(groups[0].actors).toEqual([A, C, B]);
  });

  it('keeps one entry per actor id using the newest display name', () => {
    const groups = NotificationGrouping.group([
      item({ actor: { id: 'u-a', displayName: 'Old' }, timestamp: 100 }),
      item({ actor: { id: 'u-a', displayName: 'New' }, timestamp: 200 }),
    ]);

    expect(groups[0].actors).toEqual([{ id: 'u-a', displayName: 'New' }]);
  });

  it('carries the type and reference of its items', () => {
    const reference = { id: 'img-9', fileName: 'nine.png' };
    const groups = NotificationGrouping.group([
      item({ type: 'comment', reference }),
    ]);

    expect(groups[0].type).toBe('comment');
    expect(groups[0].reference).toEqual(reference);
  });

  it('counts a null actor as a visitor without adding to actors', () => {
    const groups = NotificationGrouping.group([
      item({ actor: A, timestamp: 200 }),
      item({ actor: null, timestamp: 100 }),
    ]);

    expect(groups[0].actors).toEqual([A]);
    expect(groups[0].visitorCount).toBe(1);
  });

  it('counts every null actor item as a visitor', () => {
    const groups = NotificationGrouping.group([
      item({ actor: null }),
      item({ actor: null }),
      item({ actor: null }),
    ]);

    expect(groups[0].actors).toEqual([]);
    expect(groups[0].visitorCount).toBe(3);
    expect(groups[0].items).toHaveLength(3);
  });

  it('keeps different types on one image in separate groups', () => {
    const groups = NotificationGrouping.group([
      item({ type: 'comment', timestamp: 200 }),
      item({ type: 'added_to_bookmarks', timestamp: 100 }),
    ]);

    expect(groups.map((g) => g.key)).toEqual([
      'comment:img-1',
      'added_to_bookmarks:img-1',
    ]);
    expect(groups.every((g) => g.items.length === 1)).toBe(true);
  });

  it('keeps the same type on different images in separate groups', () => {
    const groups = NotificationGrouping.group([
      item({ reference: { id: 'img-1', fileName: 'a.png' } }),
      item({ reference: { id: 'img-2', fileName: 'b.png' } }),
    ]);

    expect(groups).toHaveLength(2);
  });

  it('does not merge comment and comment_reply', () => {
    const groups = NotificationGrouping.group([
      item({ type: 'comment' }),
      item({ type: 'comment_reply' }),
    ]);

    expect(groups).toHaveLength(2);
  });

  it('counts unread items and marks the group unread when any remain', () => {
    const groups = NotificationGrouping.group([
      item({ isRead: false }),
      item({ isRead: false }),
      item({ isRead: true }),
    ]);

    expect(groups[0].unreadCount).toBe(2);
    expect(groups[0].isRead).toBe(false);
  });

  it('marks the group read when every item is read', () => {
    const groups = NotificationGrouping.group([
      item({ isRead: true }),
      item({ isRead: true }),
    ]);

    expect(groups[0].unreadCount).toBe(0);
    expect(groups[0].isRead).toBe(true);
  });

  it('marks the group unread for a single unread item', () => {
    const groups = NotificationGrouping.group([item({ isRead: false })]);

    expect(groups[0].unreadCount).toBe(1);
    expect(groups[0].isRead).toBe(false);
  });

  it('takes latestTimestamp and latestComment from the newest item', () => {
    const groups = NotificationGrouping.group([
      item({ type: 'comment', relatedComment: comment('c1'), timestamp: 100 }),
      item({ type: 'comment', relatedComment: comment('c2'), timestamp: 300 }),
    ]);

    expect(groups[0].latestTimestamp).toBe(300);
    expect(groups[0].latestComment?.id).toBe('c2');
  });

  it('leaves latestComment null when the newest item has none', () => {
    const groups = NotificationGrouping.group([
      item({ type: 'comment', relatedComment: comment('c1'), timestamp: 100 }),
      item({ type: 'comment', relatedComment: null, timestamp: 300 }),
    ]);

    expect(groups[0].latestComment).toBeNull();
  });

  it('sorts groups by their newest item descending', () => {
    const groups = NotificationGrouping.group([
      item({ reference: { id: 'y', fileName: 'y.png' }, timestamp: 400 }),
      item({ reference: { id: 'x', fileName: 'x.png' }, timestamp: 50 }),
      item({ reference: { id: 'x', fileName: 'x.png' }, timestamp: 500 }),
    ]);

    expect(groups.map((g) => g.reference.id)).toEqual(['x', 'y']);
  });

  it('keeps input order for groups with equal latestTimestamp', () => {
    const groups = NotificationGrouping.group([
      item({ reference: { id: 'first', fileName: 'f.png' }, timestamp: 100 }),
      item({ reference: { id: 'second', fileName: 's.png' }, timestamp: 100 }),
    ]);

    expect(groups.map((g) => g.reference.id)).toEqual(['first', 'second']);
  });

  it('returns an empty list for no items', () => {
    expect(NotificationGrouping.group([])).toEqual([]);
  });

  it('does not mutate the input', () => {
    const items = [
      item({ actor: B, timestamp: 100 }),
      item({ actor: A, timestamp: 300 }),
      item({ actor: null, timestamp: 200 }),
    ];
    const snapshot = structuredClone(items);

    NotificationGrouping.group(items);

    expect(items).toEqual(snapshot);
  });
});
