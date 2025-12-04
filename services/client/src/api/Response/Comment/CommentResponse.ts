import type { ListingMetadata } from '@slink/api/Response/Common/ListingMetadata';

export interface CommentAuthor {
  id: string;
  displayName: string;
  email: string;
}

export interface ReferencedComment {
  id: string;
  author: CommentAuthor;
  isDeleted: boolean;
  displayContent: string;
}

export interface CommentItem {
  id: string;
  content: string;
  displayContent: string;
  author: CommentAuthor;
  referencedComment: ReferencedComment | null;
  createdAt: {
    formattedDate: string;
    timestamp: number;
  };
  updatedAt: {
    formattedDate: string;
    timestamp: number;
  } | null;
  isDeleted: boolean;
  isEdited: boolean;
  canEdit: boolean;
}

export interface CommentListingResponse {
  data: CommentItem[];
  meta: ListingMetadata;
}
