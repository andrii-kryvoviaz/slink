import type { Tag } from '@slink/api/Resources/TagResource';
import type { CollectionResponse, ImageListingItem } from '@slink/api/Response';

import type { PendingMultiSelection } from '@slink/lib/state/PendingSelectionState.svelte';
import type { SelectionState } from '@slink/lib/state/SelectionState.svelte';

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

  constructor(selection: SelectionState, getItems: () => ImageListingItem[]) {
    this._ctx = new BatchContext(selection, getItems);
  }

  updateVisibility = (isPublic: boolean) =>
    updateVisibility(this._ctx, isPublic);
  download = () => download(this._ctx);
  delete = (preserveOnDisk: boolean) => batchDelete(this._ctx, preserveOnDisk);
  reassignTags = (selection: PendingMultiSelection, added: Tag[]) =>
    reassignTags(this._ctx, selection, added);
  reassignCollections = (
    selection: PendingMultiSelection,
    added: CollectionResponse[],
  ) => reassignCollections(this._ctx, selection, added);
}

export const createBatchActionsState = (
  selection: SelectionState,
  getItems: () => ImageListingItem[],
) => new BatchActionsState(selection, getItems);
