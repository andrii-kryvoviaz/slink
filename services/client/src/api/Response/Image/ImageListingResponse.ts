import type { Tag } from '@slink/api/Resources/TagResource';
import type { ListingMetadata } from '@slink/api/Response/Common/ListingMetadata';

export type LicenseInfo = {
  id: string;
  title: string;
  name: string;
  description: string;
  url: string | null;
  isCreativeCommons: boolean;
};

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
  license: LicenseInfo;
  bookmarkCount: number;
  isBookmarked?: boolean;
  tags?: Tag[];
};

export type ImageListingResponse = {
  meta: ListingMetadata;
  data: ImageListingItem[];
};

export type ImagePlainListingResponse = {
  data: ImageListingItem[];
};
