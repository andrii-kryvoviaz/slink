import { ApiClient } from '@slink/api';

import type {
  GroupedNotification,
  NotificationItem,
} from '@slink/api/Response';

import { AbstractPaginatedFeed } from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';
import type {
  LoadParams,
  PaginatedResponse,
  SearchParams,
} from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';
import { useState } from '@slink/lib/state/core/ContextAwareState';

const NOTIFICATION_FEED_KEY = Symbol('notificationFeed');

function groupNotifications(items: NotificationItem[]): GroupedNotification[] {
  const groups = new Map<string, GroupedNotification>();

  for (const item of items) {
    const key = `${item.type}:${item.reference.id}:${item.actor?.id ?? 'unknown'}`;

    if (groups.has(key)) {
      const group = groups.get(key)!;
      group.items.push(item);
      if (item.createdAt.timestamp > group.latestTimestamp) {
        group.latestTimestamp = item.createdAt.timestamp;
        group.latestComment = item.relatedComment;
      }
      if (!item.isRead) {
        group.unreadCount++;
        group.isRead = false;
      }
    } else {
      groups.set(key, {
        key,
        type: item.type,
        reference: item.reference,
        actor: item.actor,
        items: [item],
        latestComment: item.relatedComment,
        latestTimestamp: item.createdAt.timestamp,
        unreadCount: item.isRead ? 0 : 1,
        isRead: item.isRead,
      });
    }
  }

  return Array.from(groups.values()).sort(
    (a, b) => b.latestTimestamp - a.latestTimestamp,
  );
}

class NotificationFeed extends AbstractPaginatedFeed<NotificationItem> {
  private _unreadCount: number = $state(0);

  public constructor() {
    super({
      defaultPageSize: 50,
      useCursor: false,
      appendMode: 'always',
    });
  }

  protected async fetchData(
    params: LoadParams & SearchParams,
  ): Promise<PaginatedResponse<NotificationItem>> {
    const { page = 1, limit = 50 } = params;
    const response = await ApiClient.notification.getNotifications(page, limit);

    return {
      data: response.data,
      meta: {
        page: response.meta.page,
        size: response.meta.size,
        total: response.meta.total,
      },
    };
  }

  protected _getItemId(item: NotificationItem): string {
    return item.id;
  }

  public get unreadCount(): number {
    return this._unreadCount;
  }

  public get groupedItems(): GroupedNotification[] {
    return groupNotifications(this._items);
  }

  public async loadUnreadCount(): Promise<void> {
    const response = await ApiClient.notification.getUnreadCount();
    this._unreadCount = response.count;
  }

  public async markAsRead(notificationId: string): Promise<void> {
    await ApiClient.notification.markAsRead(notificationId);
    const index = this._items.findIndex((item) => item.id === notificationId);
    if (index !== -1 && !this._items[index].isRead) {
      this._items[index] = { ...this._items[index], isRead: true };
      this._unreadCount = Math.max(0, this._unreadCount - 1);
    }
  }

  public async markAllAsRead(): Promise<void> {
    await ApiClient.notification.markAllAsRead();
    this._items = this._items.map((item) => ({ ...item, isRead: true }));
    this._unreadCount = 0;
  }

  public override async load(
    params: LoadParams & SearchParams = {},
    options?: Parameters<typeof this.fetch>[2],
  ): Promise<void> {
    const { page = this._meta.page } = params;

    if (this.isDirty && page === this._meta.page) {
      return;
    }

    await super.load(params, options);
    await this.loadUnreadCount();
  }
}

export const useNotificationFeed = () => {
  return useState(NOTIFICATION_FEED_KEY, new NotificationFeed());
};
