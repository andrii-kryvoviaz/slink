import type { RequestState } from '@slink/api/ReactiveState';

export function bindRequestState<T>(state: RequestState<T>) {
  let isLoading = $state(false);
  let error = $state<Error | null>(null);
  let data = $state<T | null>(null);

  const unsubscribes = [
    state.isLoading.subscribe((v) => (isLoading = v)),
    state.error.subscribe((v) => (error = v)),
    state.data.subscribe((v) => (data = v)),
  ];

  return {
    get isLoading() {
      return isLoading;
    },
    get error() {
      return error;
    },
    get data() {
      return data;
    },
    run: state.run,
    reset: state.reset,
    dispose() {
      unsubscribes.forEach((u) => u());
    },
  };
}
