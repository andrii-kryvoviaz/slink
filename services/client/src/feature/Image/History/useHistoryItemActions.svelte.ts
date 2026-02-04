import type { ImageListingItem } from '@slink/api/Response';

import type { ImageSelectionState } from '@slink/lib/state/ImageSelectionState.svelte';

import type { HistoryItemState } from './HistoryView.types';

interface HistoryItemActionsConfig {
  getSelectionState: () => ImageSelectionState | undefined;
  onDelete?: (id: string) => void;
  onCollectionChange?: (imageId: string, collectionIds: string[]) => void;
  onSelectionChange?: (id: string) => void;
}

export function useHistoryItemActions(config: HistoryItemActionsConfig) {
  const handleItemClick = (e: MouseEvent, item: ImageListingItem) => {
    const selectionState = config.getSelectionState();
    if (selectionState?.isSelectionMode) {
      e.preventDefault();
      selectionState.toggle(item.id);
      config.onSelectionChange?.(item.id);
    }
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

  return { handleItemClick, handleDelete, getItemState };
}
