import type {
  NotificationActor,
  NotificationItem,
  NotificationReference,
  NotificationRelatedComment,
  NotificationType,
} from '@slink/api/Response';

export type NotificationActorItem = NotificationItem & {
  actor: NotificationActor;
};

export interface NotificationGroup {
  key: string;
  type: NotificationType;
  reference: NotificationReference;
  actors: NotificationActor[];
  actorItems: NotificationActorItem[];
  visitorCount: number;
  latestVisitorItem: NotificationItem | null;
  actorTotal: number;
  hasSingleAuthor: boolean;
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
      const bucket = buckets.get(key);
      if (bucket) {
        bucket.push(item);
      } else {
        buckets.set(key, [item]);
      }
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
    const actorItems = this._actorItems(items);
    const visitorCount = items.filter((item) => item.actor === null).length;

    return {
      key,
      type: newest.type,
      reference: newest.reference,
      actors: actorItems.map((item) => item.actor),
      actorItems,
      visitorCount,
      latestVisitorItem: items.find((item) => item.actor === null) ?? null,
      actorTotal: actorItems.length + visitorCount,
      hasSingleAuthor: actorItems.length === 1 && visitorCount === 0,
      items,
      latestTimestamp: newest.createdAt.timestamp,
      latestComment: newest.relatedComment,
      unreadCount,
      isRead: unreadCount === 0,
    };
  }

  private static _actorItems(
    items: NotificationItem[],
  ): NotificationActorItem[] {
    const seen = new Set<string>();
    const actorItems: NotificationActorItem[] = [];

    for (const item of items) {
      if (!this._hasActor(item) || seen.has(item.actor.id)) continue;

      seen.add(item.actor.id);
      actorItems.push(item);
    }

    return actorItems;
  }

  private static _hasActor(
    item: NotificationItem,
  ): item is NotificationActorItem {
    return item.actor !== null;
  }
}
