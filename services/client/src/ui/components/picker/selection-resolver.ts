import type { SelectionState } from '@slink/lib/state/PendingSelectionState.svelte';

export type SelectionResolver = (id: string) => boolean | SelectionState;

export function createSelectionResolver(
  selectedIds: string[],
  getItemState?: (id: string) => SelectionState,
): SelectionResolver {
  if (getItemState) return getItemState;
  return (id) => selectedIds.includes(id);
}
