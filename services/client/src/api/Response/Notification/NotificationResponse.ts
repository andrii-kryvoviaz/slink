import type { ListingMetadata } from '@slink/api/Response/Common/ListingMetadata';

export type NotificationType =
  | 'comment'
  | 'comment_reply'
  | 'added_to_favorite';

export interface NotificationActor {
  id: string;
  email: string;
  username: string;
  displayName: string;
}

export interface NotificationReference {
  id: string;
  fileName: string;
}

export interface NotificationRelatedComment {
  id: string;
  content: string;
  isDeleted: boolean;
}

export interface NotificationItem {
  id: string;
  type: NotificationType;
  message: string;
  reference: NotificationReference;
  relatedComment: NotificationRelatedComment | null;
  actor: NotificationActor | null;
  isRead: boolean;
  createdAt: {
    formattedDate: string;
    timestamp: number;
  };
}

export interface GroupedNotification {
  key: string;
  type: NotificationType;
  reference: NotificationReference;
  actor: NotificationActor | null;
  items: NotificationItem[];
  latestComment: NotificationRelatedComment | null;
  latestTimestamp: number;
  unreadCount: number;
  isRead: boolean;
}

export interface NotificationListingResponse {
  meta: ListingMetadata;
  data: NotificationItem[];
}

export interface UnreadCountResponse {
  count: number;
}
