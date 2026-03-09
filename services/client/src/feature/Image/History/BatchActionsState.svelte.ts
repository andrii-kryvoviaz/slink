import type { ImageListingItem } from '@slink/api/Response';

import type { ImageSelectionState } from '@slink/lib/state/ImageSelectionState.svelte';
import type { PendingMultiSelection } from '@slink/lib/state/PendingSelectionState.svelte';

import { BatchContext } from './BatchContext.svelte';
import { batchDelete } from './batch-actions/delete';
import { download } from './batch-actions/download';
import { reassignCollections, reassignTags } from './batch-actions/reassign';
import { updateVisibility } from './batch-actions/visibility';

export class BatchActionsState {
  private _ctx: BatchContext;

  get isLoading() {
    return this._ctx.isLoading;
  }
  get tagAssignmentCounts() {
    return this._ctx.tagAssignmentCounts;
  }
  get collectionAssignmentCounts() {
    return this._ctx.collectionAssignmentCounts;
  }
  get selectedItemCount() {
    return this._ctx.selectedItemCount;
  }

  constructor(
    selection: ImageSelectionState,
    getItems: () => ImageListingItem[],
  ) {
    this._ctx = new BatchContext(selection, getItems);
  }

  updateVisibility = (isPublic: boolean) =>
    updateVisibility(this._ctx, isPublic);
  download = () => download(this._ctx);
  delete = (preserveOnDisk: boolean) => batchDelete(this._ctx, preserveOnDisk);
  reassignTags = (selection: PendingMultiSelection) =>
    reassignTags(this._ctx, selection);
  reassignCollections = (selection: PendingMultiSelection) =>
    reassignCollections(this._ctx, selection);
}

export const createBatchActionsState = (
  selection: ImageSelectionState,
  getItems: () => ImageListingItem[],
) => new BatchActionsState(selection, getItems);
