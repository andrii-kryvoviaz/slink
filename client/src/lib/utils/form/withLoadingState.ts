import type { ActionResult } from '@sveltejs/kit';

import { applyAction } from '$app/forms';
import { invalidateAll } from '$app/navigation';
import type { Writable } from 'svelte/store';

export function withLoadingState(
  loading: Writable<boolean>,
  invalidate: boolean = false
) {
  return () => {
    loading.set(true);

    return async ({ result }: { update: Function; result: ActionResult }) => {
      loading.set(false);

      if (invalidate) {
        await invalidateAll();
      }

      await applyAction(result);
    };
  };
}
