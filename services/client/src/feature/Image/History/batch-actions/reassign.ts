import { ApiClient } from '@slink/api';

import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';

import type { Tag } from '@slink/api/Resources/TagResource';
import type { ImageListingItem } from '@slink/api/Response';
import type {
  CollectionReference,
  CollectionResponse,
} from '@slink/api/Response/Collection/CollectionResponse';

import type { PendingMultiSelection } from '@slink/lib/state/PendingSelectionState.svelte';

import type { BatchContext } from '../BatchContext.svelte';

type AssignmentEntry = { tagIds?: string[]; collectionIds?: string[] };

interface ReassignField<
  TReference extends { id: string },
  TOption extends { id: string },
> {
  key: 'tags' | 'collections';
  current: (item: ImageListingItem) => TReference[];
  toReference: (option: TOption) => TReference;
  buildEntry: (ids: string[]) => AssignmentEntry;
  error: string;
}

function mergeUnique<T extends { id: string }>(kept: T[], added: T[]): T[] {
  const ids = new Set(kept.map((el) => el.id));
  return [...kept, ...added.filter((el) => !ids.has(el.id))];
}

interface ReassignmentPlan<TReference> {
  payload: Record<string, AssignmentEntry>;
  nextByImage: Map<string, TReference[]>;
}

function planReassignment<
  TReference extends { id: string },
  TOption extends { id: string },
>(
  items: ImageListingItem[],
  selection: PendingMultiSelection,
  options: TOption[],
  field: ReassignField<TReference, TOption>,
): ReassignmentPlan<TReference> {
  const optionsById = new Map(options.map((option) => [option.id, option]));
  const added = selection.addedIds
    .map((id) => optionsById.get(id))
    .filter((option): option is TOption => option !== undefined)
    .map(field.toReference);
  const removedIds = new Set(selection.removedIds);

  const nextByImage = new Map<string, TReference[]>();
  const payload: Record<string, AssignmentEntry> = {};

  for (const item of items) {
    const kept = field.current(item).filter((el) => !removedIds.has(el.id));
    const next = mergeUnique(kept, added);
    nextByImage.set(item.id, next);
    payload[item.id] = field.buildEntry(next.map((el) => el.id));
  }

  return { payload, nextByImage };
}

async function reassign<
  TReference extends { id: string },
  TOption extends { id: string },
>(
  ctx: BatchContext,
  selection: PendingMultiSelection,
  options: TOption[],
  field: ReassignField<TReference, TOption>,
): Promise<void> {
  if (!selection.hasChanges) return;

  const plan = planReassignment(ctx.selectedItems, selection, options, field);

  const result = await ctx.execute(() =>
    ApiClient.image.batchReassign(plan.payload),
  );
  if (!result) {
    toast.error(field.error);
    return;
  }
  if (!ctx.notifyBatchResult(result)) return;

  for (const id of result.processed) {
    ctx.historyFeed.update(id, {
      [field.key]: plan.nextByImage.get(id),
    } as Partial<ImageListingItem>);
  }

  ctx.exitSelection();
}

const tagField: ReassignField<Tag, Tag> = {
  key: 'tags',
  current: (item) => item.tags ?? [],
  toReference: (tag) => tag,
  buildEntry: (ids) => ({ tagIds: ids }),
  error: 'Failed to reassign tags. Please try again later.',
};

const collectionField: ReassignField<CollectionReference, CollectionResponse> =
  {
    key: 'collections',
    current: (item) => item.collections ?? [],
    toReference: ({ id, name }) => ({ id, name }),
    buildEntry: (ids) => ({ collectionIds: ids }),
    error: 'Failed to reassign collections. Please try again later.',
  };

export const reassignTags = (
  ctx: BatchContext,
  selection: PendingMultiSelection,
  options: Tag[],
) => reassign(ctx, selection, options, tagField);

export const reassignCollections = (
  ctx: BatchContext,
  selection: PendingMultiSelection,
  options: CollectionResponse[],
) => reassign(ctx, selection, options, collectionField);
