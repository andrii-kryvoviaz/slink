import type { ListingMetadata } from '@slink/api/Response/Common/ListingMetadata';
import type { ImageListingItem } from '@slink/api/Response/Image/ImageListingResponse';

export interface BookmarkItem {
  id: string;
  image: Pick<ImageListingItem, 'id'> | ImageListingItem;
  createdAt: {
    formattedDate: string;
    timestamp: number;
  };
}

export interface BookmarkListingResponse {
  data: BookmarkItem[];
  meta: ListingMetadata;
}

export interface BookmarkStatusResponse {
  isBookmarked: boolean;
}

export interface BookmarkerItem {
  id: string;
  displayName: string;
  bookmarkedAt: {
    formattedDate: string;
    timestamp: number;
  };
}

export interface BookmarkersResponse {
  data: BookmarkerItem[];
  meta: ListingMetadata;
}
