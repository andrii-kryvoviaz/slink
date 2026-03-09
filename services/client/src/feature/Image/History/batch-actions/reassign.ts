import { ApiClient } from '@slink/api';

import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';

import type { ImageListingItem } from '@slink/api/Response';

import type { PendingMultiSelection } from '@slink/lib/state/PendingSelectionState.svelte';

import type { BatchContext } from '../BatchContext.svelte';

type AssignmentEntry = { tagIds?: string[]; collectionIds?: string[] };

interface ReassignmentDescriptor {
  getExisting: (item: ImageListingItem) => string[];
  buildEntry: (ids: string[]) => AssignmentEntry;
  errorMessage: string;
  onSuccess: (
    ctx: BatchContext,
    perImageIds: Record<string, string[]>,
  ) => void | Promise<void>;
}

function applySelection(
  existing: string[],
  selection: PendingMultiSelection,
): string[] {
  const set = new Set(existing);
  for (const id of selection.addedIds) set.add(id);
  for (const id of selection.removedIds) set.delete(id);
  return [...set];
}

async function reassign(
  ctx: BatchContext,
  selection: PendingMultiSelection,
  descriptor: ReassignmentDescriptor,
): Promise<void> {
  if (!selection.hasChanges) return;

  const perImageIds: Record<string, string[]> = {};
  const apiPayload: Record<string, AssignmentEntry> = {};

  for (const item of ctx.selectedItems) {
    const ids = applySelection(descriptor.getExisting(item), selection);
    perImageIds[item.id] = ids;
    apiPayload[item.id] = descriptor.buildEntry(ids);
  }

  const result = await ctx.execute(() =>
    ApiClient.image.batchReassign(apiPayload),
  );
  if (!result) {
    toast.error(descriptor.errorMessage);
    return;
  }
  if (!ctx.notifyBatchResult(result)) return;
  await descriptor.onSuccess(ctx, perImageIds);
  ctx.exitSelection();
}

const tagDescriptor: ReassignmentDescriptor = {
  getExisting: (item) => (item.tags ?? []).map((t) => t.id),
  buildEntry: (ids) => ({ tagIds: ids }),
  errorMessage: 'Failed to reassign tags. Please try again later.',
  onSuccess: async (ctx) => {
    await ctx.historyFeed.reload();
  },
};

const collectionDescriptor: ReassignmentDescriptor = {
  getExisting: (item) => item.collectionIds ?? [],
  buildEntry: (ids) => ({ collectionIds: ids }),
  errorMessage: 'Failed to reassign collections. Please try again later.',
  onSuccess: (ctx, perImageIds) => {
    for (const [imageId, collectionIds] of Object.entries(perImageIds)) {
      ctx.historyFeed.update(imageId, { collectionIds });
    }
  },
};

export const reassignTags = (
  ctx: BatchContext,
  selection: PendingMultiSelection,
) => reassign(ctx, selection, tagDescriptor);

export const reassignCollections = (
  ctx: BatchContext,
  selection: PendingMultiSelection,
) => reassign(ctx, selection, collectionDescriptor);
