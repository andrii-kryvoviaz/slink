import type { ImageListingItem } from '@slink/api/Response';

export interface ExploreViewProps {
  items?: ImageListingItem[];
  licensingEnabled: boolean;
  userIsAdmin: boolean;
  on: {
    open: (item: ImageListingItem) => void;
    bookmarkChange: (
      image: ImageListingItem,
      isBookmarked: boolean,
      count: number,
    ) => void;
    imageUpdate: (updatedImage: ImageListingItem) => void | Promise<void>;
    imageDelete: (imageId: string) => void | Promise<void>;
  };
}
