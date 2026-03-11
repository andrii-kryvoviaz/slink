import type { Tag } from '@slink/api/Resources/TagResource';
import type { CollectionResponse } from '@slink/api/Response';

import {
  type CreateCollectionModalState,
  createCreateCollectionModalState,
} from '@slink/lib/state/CreateCollectionModalState.svelte';
import {
  type CreateTagModalState,
  createCreateTagModalState,
} from '@slink/lib/state/CreateTagModalState.svelte';
import {
  type CollectionImagePickerState,
  type TagImagePickerState,
  createCollectionPickerState,
  createImageTagPickerState,
} from '@slink/lib/state/ImagePickerState.svelte';
import {
  type PendingMultiSelection,
  createPendingMultiSelection,
} from '@slink/lib/state/PendingSelectionState.svelte';

import type { BatchActionsState } from './BatchActionsState.svelte';

interface BatchPickerDeps<TItem> {
  addItem: (item: TItem) => void;
  getCounts: () => Map<string, number>;
  getTotal: () => number;
  applyChanges: (selection: PendingMultiSelection) => Promise<void>;
}

class BatchPickerState<
  TItem,
  TPicker extends { load(): void },
  TModal extends {
    open(onSuccess?: (item: TItem) => void, onClose?: () => void): void;
  },
> {
  open: boolean = $state(false);

  readonly picker: TPicker;
  readonly modal: TModal;
  readonly selection: PendingMultiSelection;

  private _addItem: (item: TItem) => void;
  private _applyChanges: (selection: PendingMultiSelection) => Promise<void>;

  constructor(picker: TPicker, modal: TModal, deps: BatchPickerDeps<TItem>) {
    this.picker = picker;
    this.modal = modal;
    this._addItem = deps.addItem;
    this._applyChanges = deps.applyChanges;
    this.selection = createPendingMultiSelection(() => ({
      counts: deps.getCounts(),
      total: deps.getTotal(),
    }));
  }

  handleOpen = () => {
    this.picker.load();
    this.selection.reset();
  };

  handleCreateNew = () => {
    this.open = false;
    this.modal.open(
      (item: TItem) => {
        this._addItem(item);
      },
      () => {
        this.open = true;
      },
    );
  };

  apply = async () => {
    if (!this.selection.hasChanges) return;
    await this._applyChanges(this.selection);
    this.selection.reset();
  };
}

export const createBatchCollectionPickerState = (
  batchActions: BatchActionsState,
) => {
  const picker = createCollectionPickerState();
  const modal = createCreateCollectionModalState();
  return new BatchPickerState<
    CollectionResponse,
    CollectionImagePickerState,
    CreateCollectionModalState
  >(picker, modal, {
    addItem: (collection) => picker.addItem(collection),
    getCounts: () => batchActions.collectionAssignmentCounts,
    getTotal: () => batchActions.selectedItemCount,
    applyChanges: (s) => batchActions.reassignCollections(s),
  });
};

export const createBatchTagPickerState = (batchActions: BatchActionsState) => {
  const picker = createImageTagPickerState();
  const modal = createCreateTagModalState();
  return new BatchPickerState<Tag, TagImagePickerState, CreateTagModalState>(
    picker,
    modal,
    {
      addItem: (tag) => picker.addItem(tag),
      getCounts: () => batchActions.tagAssignmentCounts,
      getTotal: () => batchActions.selectedItemCount,
      applyChanges: (s) => batchActions.reassignTags(s),
    },
  );
};

export type BatchCollectionPickerState = ReturnType<
  typeof createBatchCollectionPickerState
>;
export type BatchTagPickerState = ReturnType<typeof createBatchTagPickerState>;
