import type { ListingMetadata } from '@slink/api/Response/Common/ListingMetadata';

export type ImageListingItem = {
  id: string;
  owner: {
    id: string;
    email: string;
    displayName: string;
  };
  attributes: {
    fileName: string;
    description: string;
    isPublic: boolean;
    createdAt: {
      formattedDate: string;
      timestamp: number;
    };
    views: number;
  };
  metadata: {
    size: number;
    mimeType: string;
    width: number;
    height: number;
  };
};

export type ImageListingResponse = {
  meta: ListingMetadata;
  data: ImageListingItem[];
};

export type ImagePlainListingResponse = {
  data: ImageListingItem[];
};
