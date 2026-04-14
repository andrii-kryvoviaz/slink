import { ApiClient } from '@slink/api';

import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';

import { messages } from '@slink/lib/utils/i18n/messages/toast.language';

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
    toast.error(messages.image.failedToUpdateVisibility);
    return;
  }
  if (!ctx.notifyBatchResult(result)) return;
  result.processed.forEach((id) => {
    ctx.historyFeed.update(id, { attributes: { isPublic } });
  });
  ctx.exitSelection();
}
