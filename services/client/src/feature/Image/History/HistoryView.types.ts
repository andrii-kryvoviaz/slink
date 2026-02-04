import type { ImageListingItem } from '@slink/api/Response';

import type { ImageSelectionState } from '@slink/lib/state/ImageSelectionState.svelte';

export interface HistoryViewProps {
  items?: ImageListingItem[];
  selectionState?: ImageSelectionState;
  on?: {
    delete: (id: string) => void;
    collectionChange: (imageId: string, collectionIds: string[]) => void;
    selectionChange?: (id: string) => void;
  };
}

export interface HistoryItemState {
  isSelected: boolean;
  isSelectionMode: boolean;
  selectionAriaLabel: string;
  itemHref: string;
}
