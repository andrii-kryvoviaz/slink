import type {
  NotificationActor,
  NotificationItem,
  NotificationReference,
  NotificationRelatedComment,
  NotificationType,
} from '@slink/api/Response';

export interface NotificationGroup {
  key: string;
  type: NotificationType;
  reference: NotificationReference;
  actors: NotificationActor[];
  visitorCount: number;
  items: NotificationItem[];
  latestTimestamp: number;
  latestComment: NotificationRelatedComment | null;
  unreadCount: number;
  isRead: boolean;
}

export class NotificationGrouping {
  static group(items: NotificationItem[]): NotificationGroup[] {
    const buckets = new Map<string, NotificationItem[]>();

    for (const item of items) {
      const key = `${item.type}:${item.reference.id}`;
      buckets.set(key, [...(buckets.get(key) ?? []), item]);
    }

    return Array.from(buckets, ([key, bucket]) =>
      this._toGroup(key, bucket),
    ).sort((a, b) => b.latestTimestamp - a.latestTimestamp);
  }

  private static _toGroup(
    key: string,
    bucket: NotificationItem[],
  ): NotificationGroup {
    const items = bucket.toSorted(
      (a, b) => b.createdAt.timestamp - a.createdAt.timestamp,
    );
    const newest = items[0];
    const unreadCount = items.filter((item) => !item.isRead).length;

    return {
      key,
      type: newest.type,
      reference: newest.reference,
      actors: this._distinctActors(items),
      visitorCount: items.filter((item) => item.actor === null).length,
      items,
      latestTimestamp: newest.createdAt.timestamp,
      latestComment: newest.relatedComment,
      unreadCount,
      isRead: unreadCount === 0,
    };
  }

  private static _distinctActors(
    items: NotificationItem[],
  ): NotificationActor[] {
    const seen = new Map<string, NotificationActor>();

    for (const { actor } of items) {
      if (actor && !seen.has(actor.id)) {
        seen.set(actor.id, actor);
      }
    }

    return Array.from(seen.values());
  }
}
