import { ApiClient } from '@slink/api';

import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';

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
    toast.error('Failed to delete images. Please try again later.');
    return;
  }

  if (result.deleted.length > 0) {
    result.deleted.forEach((id) => ctx.historyFeed.removeItem(id));
    ctx.selection.removeIds(result.deleted);
    toast.success(
      `Successfully deleted ${pluralize(result.deleted.length, 'image')} from history`,
    );

    if (!ctx.historyFeed.hasItems && ctx.historyFeed.hasMore) {
      ctx.historyFeed.reset();
      await ctx.historyFeed.load();
    }
  }

  if (result.failed.length > 0) {
    toast.error(`Failed to delete ${pluralize(result.failed.length, 'image')}`);
  }

  ctx.exitSelection();
}
