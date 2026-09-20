import type { ListingMetadata } from '@slink/api/Response/Common/ListingMetadata';

export type NotificationType =
  'comment' | 'comment_reply' | 'added_to_bookmarks';

export interface NotificationListQuery {
  type?: NotificationType;
  unread?: boolean;
}

export interface NotificationActor {
  id: string;
  displayName: string;
}

export interface NotificationReference {
  id: string;
  fileName: string;
}

export interface NotificationReferencedComment {
  id: string;
  content: string;
}

export interface NotificationRelatedComment {
  id: string;
  content: string;
  isDeleted: boolean;
  referencedComment: NotificationReferencedComment | null;
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
