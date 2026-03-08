import { ApiClient } from '@slink/api';

import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';

import type { BatchContext } from '../BatchContext.svelte';

export async function updateVisibility(
  ctx: BatchContext,
  isPublic: boolean,
): Promise<void> {
  const selectedIds = ctx.selectedIds;
  const result = await ctx.execute(() =>
    ApiClient.image.batch(selectedIds, { isPublic }),
  );
  if (!result) {
    toast.error('Failed to update visibility. Please try again later.');
    return;
  }
  if (!ctx.notifyBatchResult(result)) return;
  result.processed.forEach((id) => {
    ctx.historyFeed.update(id, { attributes: { isPublic } });
  });
  ctx.exitSelection();
}
