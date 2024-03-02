type ListingMetadata = {
  size: number;
  page: number;
  total: number;
};

type ImageListingItem = {
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
