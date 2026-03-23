import type { ImageListingItem } from '@slink/api/Response';
import type { CollectionReference } from '@slink/api/Response/Collection/CollectionResponse';

import type { ImageSelectionState } from '@slink/lib/state/ImageSelectionState.svelte';

import type { HistoryItemState } from './HistoryView.types';

interface HistoryItemActionsConfig {
  getSelectionState: () => ImageSelectionState | undefined;
  onDelete?: (id: string) => void;
  onCollectionChange?: (
    imageId: string,
    collections: CollectionReference[],
  ) => void;
  onSelectionChange?: (id: string) => void;
}

export function useHistoryItemActions(config: HistoryItemActionsConfig) {
  const handleSelect = (e: MouseEvent, item: ImageListingItem) => {
    e.preventDefault();
    const selectionState = config.getSelectionState();
    if (!selectionState) return;

    selectionState.select(item.id);
    config.onSelectionChange?.(item.id);
  };

  const handleKeydown = (e: KeyboardEvent, item: ImageListingItem) => {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    const selectionState = config.getSelectionState();
    if (!selectionState) return;
    selectionState.select(item.id);
  };

  const handleDelete = (id: string) => {
    config.onDelete?.(id);
  };

  const getItemState = (item: ImageListingItem): HistoryItemState => {
    const selectionState = config.getSelectionState();
    const isSelected = selectionState?.isSelected(item.id) ?? false;
    const isSelectionMode = selectionState?.isSelectionMode ?? false;

    return {
      isSelected,
      isSelectionMode,
      selectionAriaLabel: isSelected ? 'Deselect image' : 'Select image',
      itemHref: isSelectionMode ? '' : `/info/${item.id}`,
    };
  };

  return { handleSelect, handleKeydown, handleDelete, getItemState };
}
