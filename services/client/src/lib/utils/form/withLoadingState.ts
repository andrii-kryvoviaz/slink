import type { ActionResult } from '@sveltejs/kit';

import { invalidateAll } from '$app/navigation';
import type { Writable } from 'svelte/store';

type WithLoadingStateOptions = {
  invalidate?: boolean;
  onSuccess?: (data: Record<string, unknown>) => void | Promise<void>;
  onError?: (data: Record<string, unknown>) => void | Promise<void>;
};

export function withLoadingState(
  loading: Writable<boolean>,
  options: WithLoadingStateOptions = {},
) {
  return () => {
    loading.set(true);

    return async ({
      result,
      update,
    }: {
      result: ActionResult;
      update: Function;
    }) => {
      loading.set(false);

      if (options.invalidate) {
        await invalidateAll();
      }

      await update();

      if (result.type === 'success') {
        await options.onSuccess?.(result.data as Record<string, unknown>);
      }

      if (result.type === 'failure') {
        await options.onError?.(result.data as Record<string, unknown>);
      }
    };
  };
}
