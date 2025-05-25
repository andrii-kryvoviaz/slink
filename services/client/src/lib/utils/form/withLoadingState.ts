import type { ActionResult } from '@sveltejs/kit';
import type { Writable } from 'svelte/store';

import { invalidateAll } from '$app/navigation';

export function withLoadingState(
  loading: Writable<boolean>,
  invalidate: boolean = false,
) {
  return () => {
    loading.set(true);

    return async ({
      result,
      update,
    }: {
      update: Function;
      result: ActionResult;
    }) => {
      loading.set(false);

      if (invalidate) {
        await invalidateAll();
      }

      await update();
    };
  };
}
