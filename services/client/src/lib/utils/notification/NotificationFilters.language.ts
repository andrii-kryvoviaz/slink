import type {
  NotificationListQuery,
  NotificationType,
} from '@slink/api/Response';

import { localize } from '@slink/lib/utils/i18n';

export type NotificationFilterId = 'all' | 'unread' | NotificationType;

export interface NotificationFilter {
  readonly id: NotificationFilterId;
  readonly label: string;
  readonly query: NotificationListQuery;
}

const filters: readonly NotificationFilter[] = [
  {
    id: 'all',
    get label() {
      return localize('All');
    },
    query: {},
  },
  {
    id: 'unread',
    get label() {
      return localize('Unread');
    },
    query: { unread: true },
  },
  {
    id: 'comment',
    get label() {
      return localize('Comments');
    },
    query: { type: 'comment' },
  },
  {
    id: 'comment_reply',
    get label() {
      return localize('Replies');
    },
    query: { type: 'comment_reply' },
  },
  {
    id: 'added_to_bookmarks',
    get label() {
      return localize('Bookmarks');
    },
    query: { type: 'added_to_bookmarks' },
  },
];

export class NotificationFilters {
  public static readonly list = filters;

  public static queryOf(id: NotificationFilterId): NotificationListQuery {
    return this.list.find((filter) => filter.id === id)?.query ?? {};
  }
}
