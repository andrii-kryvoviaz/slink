import type { Writable } from 'svelte/store';

export function withLoadingState(loading: Writable<boolean>) {
  return () => {
    loading.set(true);
    return async ({ update }: { update: Function }) => {
      loading.set(false);
      update();
    };
  };
}
