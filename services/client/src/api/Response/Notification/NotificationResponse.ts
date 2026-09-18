import type { ListingMetadata } from '@slink/api/Response/Common/ListingMetadata';

export type NotificationType =
  'comment' | 'comment_reply' | 'added_to_bookmarks';

export interface NotificationActor {
  id: string;
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

export interface NotificationListingResponse {
  meta: ListingMetadata;
  data: NotificationItem[];
}

export interface UnreadCountResponse {
  count: number;
}
