import type { Tag } from '@slink/api/Resources/TagResource';
import type { ImageListingItem } from '@slink/api/Response';
import type { CollectionReference } from '@slink/api/Response/Collection/CollectionResponse';

import type { ImageSelectionState } from '@slink/lib/state/ImageSelectionState.svelte';

export interface HistoryViewProps {
  items?: ImageListingItem[];
  selectionState?: ImageSelectionState;
  on?: {
    delete: (id: string) => void;
    collectionChange: (
      imageId: string,
      collections: CollectionReference[],
    ) => void;
    tagChange?: (imageId: string, tags: Tag[]) => void;
    selectionChange?: (id: string) => void;
  };
}

export interface HistoryItemState {
  isSelected: boolean;
  isSelectionMode: boolean;
  selectionAriaLabel: string;
  itemHref: string;
}
