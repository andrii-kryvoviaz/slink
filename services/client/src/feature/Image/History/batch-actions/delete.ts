import { ApiClient } from '@slink/api';

import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';

import { messages } from '@slink/lib/utils/i18n/messages/toast.language';
import { pluralize } from '@slink/lib/utils/string/pluralize';

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
    result.deleted.forEach((id) => ctx.historyFeed.removeItem(id));
    ctx.selection.removeIds(result.deleted);
    toast.success(
      messages.image.deletedFromHistory(
        pluralize(result.deleted.length, 'image'),
      ),
    );

    if (!ctx.historyFeed.hasItems && ctx.historyFeed.hasMore) {
      ctx.historyFeed.reset();
      await ctx.historyFeed.load();
    }
  }

  if (result.failed.length > 0) {
    toast.error(
      messages.image.failedToDeleteCount(
        pluralize(result.failed.length, 'image'),
      ),
    );
  }

  ctx.exitSelection();
}
