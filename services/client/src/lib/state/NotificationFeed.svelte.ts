import { ApiClient } from '@slink/api';

import type { NotificationItem } from '@slink/api/Response';

import { AbstractPaginatedFeed } from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';
import type {
  LoadParams,
  PaginatedResponse,
  SearchParams,
} from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';
import { useState } from '@slink/lib/state/core/ContextAwareState';

import {
  type NotificationDayBucket,
  NotificationDayBuckets,
  type NotificationGroup,
  NotificationGrouping,
} from '@slink/utils/notification';

const NOTIFICATION_FEED_KEY = Symbol('notificationFeed');

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

  public get groupedItems(): NotificationGroup[] {
    return NotificationGrouping.group(this._items);
  }

  public get dayBuckets(): NotificationDayBucket[] {
    return NotificationDayBuckets.fromGroups(this.groupedItems);
  }

  public async loadUnreadCount(): Promise<void> {
    const response = await ApiClient.notification.getUnreadCount();
    this._unreadCount = response.count;
  }

  public async markAsRead(notificationId: string): Promise<void> {
    await ApiClient.notification.markAsRead(notificationId);

    const item = this.get(notificationId);
    if (!item || item.isRead) {
      return;
    }

    this.update(notificationId, { isRead: true });
    this._unreadCount = Math.max(0, this._unreadCount - 1);
  }

  public async markGroupAsRead(group: NotificationGroup): Promise<void> {
    const ids = group.items
      .filter((item) => !item.isRead)
      .map((item) => item.id);

    if (ids.length === 0) return;

    await Promise.all(ids.map((id) => ApiClient.notification.markAsRead(id)));

    for (const id of ids) {
      this.update(id, { isRead: true });
    }
    this._unreadCount = Math.max(0, this._unreadCount - ids.length);
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
