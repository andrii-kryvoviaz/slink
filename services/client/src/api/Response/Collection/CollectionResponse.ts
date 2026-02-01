import type { ListingMetadata } from '@slink/api/Response/Common/ListingMetadata';
import type { ImageListingItem } from '@slink/api/Response/Image/ImageListingResponse';
import type { ShareResponse } from '@slink/api/Response/Share/ShareResponse';

export type ItemType = 'image' | 'video';

export interface CollectionItem {
  id: string;
  itemId: string;
  itemType: ItemType;
  position: number;
  addedAt: {
    formattedDate: string;
    timestamp: number;
  };
  item?: ImageListingItem;
}

export interface CollectionResponse {
  id: string;
  uuid: string;
  userId: string;
  name: string;
  description: string | null;
  itemCount?: number;
  coverImage?: string | null;
  shareInfo?: ShareResponse;
  createdAt: {
    formattedDate: string;
    timestamp: number;
  };
  updatedAt: {
    formattedDate: string;
    timestamp: number;
  } | null;
}

export interface CollectionListingResponse {
  data: CollectionResponse[];
  meta: ListingMetadata;
}

export interface CollectionItemsResponse {
  data: CollectionItem[];
  meta: ListingMetadata;
}
