import { useUploadHistoryFeed } from '$lib/state/UploadHistoryFeed.svelte.js';
import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';

import type { ImageListingItem } from '@slink/api/Response';

import type { ImageSelectionState } from '@slink/lib/state/ImageSelectionState.svelte';
import { pluralize } from '@slink/lib/utils/string/pluralize';

export type BatchResult = {
  processed: string[];
  failed: Array<{ id: string; reason: string }>;
};

function countAssignments<T>(
  items: T[],
  getIds: (item: T) => string[],
): Map<string, number> {
  const map = new Map<string, number>();
  for (const item of items) {
    for (const id of getIds(item)) {
      map.set(id, (map.get(id) ?? 0) + 1);
    }
  }
  return map;
}

export class BatchContext {
  private _selection!: ImageSelectionState;
  private _getItems!: () => ImageListingItem[];
  private _historyFeed = useUploadHistoryFeed();
  _isLoading: boolean = $state(false);

  private readonly _selectedItems = $derived(
    this._getItems().filter((item) =>
      this._selection.selectedIds.includes(item.id),
    ),
  );

  readonly tagAssignmentCounts = $derived.by(() =>
    countAssignments(this._selectedItems, (item) =>
      (item.tags ?? []).map((t) => t.id),
    ),
  );

  readonly collectionAssignmentCounts = $derived.by(() =>
    countAssignments(this._selectedItems, (item) => item.collectionIds ?? []),
  );

  readonly selectedItemCount = $derived(this._selectedItems.length);

  get isLoading() {
    return this._isLoading;
  }
  get selectedIds() {
    return this._selection.selectedIds;
  }
  get selectedItems() {
    return this._selectedItems;
  }
  get historyFeed() {
    return this._historyFeed;
  }
  get selection() {
    return this._selection;
  }

  constructor(
    selection: ImageSelectionState,
    getItems: () => ImageListingItem[],
  ) {
    this._selection = selection;
    this._getItems = getItems;
  }

  async execute<T>(fn: () => Promise<T>): Promise<T | null> {
    this._isLoading = true;
    try {
      const [result] = await Promise.all([
        fn(),
        new Promise<void>((r) => setTimeout(r, 300)),
      ]);
      return result;
    } catch {
      return null;
    } finally {
      this._isLoading = false;
    }
  }

  notifyBatchResult(result: BatchResult): boolean {
    if (result.processed.length > 0) {
      toast.success(
        `Successfully updated ${pluralize(result.processed.length, 'image')}`,
      );
    }

    if (result.failed.length > 0) {
      toast.error(
        `Failed to update ${pluralize(result.failed.length, 'image')}`,
      );
    }

    return result.processed.length > 0;
  }

  exitSelection() {
    this._selection.exitSelectionMode();
  }
}
