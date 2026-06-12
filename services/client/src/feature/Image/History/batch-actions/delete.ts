import { ApiClient } from '@slink/api';

import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';

import { messages } from '@slink/lib/utils/i18n/messages/toast.language';

import type { BatchContext } from '../BatchContext.svelte';

export async function batchDelete(
  ctx: BatchContext,
  preserveOnDisk: boolean,
): Promise<void> {
  const selectedIds = ctx.selectedIds;
  const result = await ctx.execute(() =>
    ApiClient.image.batchRemove(selectedIds, preserveOnDisk),
  );

  if (!result) {
    toast.error(messages.image.failedToDeleteBatch);
    return;
  }

  if (result.deleted.length > 0) {
    await ctx.historyFeed.removeItems(result.deleted);
    ctx.selection.removeIds(result.deleted);
    toast.success(
      messages.image.deletedFromHistory(String(result.deleted.length)),
    );
  }

  if (result.failed.length > 0) {
    toast.error(
      messages.image.failedToDeleteCount(String(result.failed.length)),
    );
  }

  ctx.exitSelection();
}
