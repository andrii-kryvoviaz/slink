import type { Snippet } from 'svelte';

import type { ImageListingItem } from '@slink/api/Response';

export type ExploreViewItem = ImageListingItem | Pick<ImageListingItem, 'id'>;

export const hasMedia = (item: ExploreViewItem): item is ImageListingItem =>
  'url' in item && Boolean(item.url);

export interface ExploreViewProps {
  items?: ExploreViewItem[];
  licensingEnabled: boolean;
  userIsAdmin: boolean;
  badge?: Snippet<[ImageListingItem]>;
  unavailable?: Snippet<[Pick<ImageListingItem, 'id'>]>;
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
