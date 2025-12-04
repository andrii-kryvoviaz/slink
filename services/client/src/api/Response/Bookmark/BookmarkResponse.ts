import type { ListingMetadata } from '@slink/api/Response/Common/ListingMetadata';
import type { ImageListingItem } from '@slink/api/Response/Image/ImageListingResponse';

export interface BookmarkImage {
  id: string;
  available: boolean;
  owner?: ImageListingItem['owner'];
  attributes?: ImageListingItem['attributes'];
  metadata?: ImageListingItem['metadata'];
}

export interface BookmarkItem {
  id: string;
  image: BookmarkImage;
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
  email: string;
  bookmarkedAt: {
    formattedDate: string;
    timestamp: number;
  };
}

export interface BookmarkersResponse {
  data: BookmarkerItem[];
  meta: ListingMetadata;
}
