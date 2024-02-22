import { type Writable, writable } from 'svelte/store';

type RequestStatus = 'idle' | 'loading' | 'finished' | 'error';

export type RequestState<T> = {
  status: Writable<RequestStatus>;
  isLoading: Writable<boolean>;
  action: (...args: any[]) => Promise<void>;
  data: Writable<T | null>;
  error: Writable<Error | null>;
  reset: () => void;
};

export function ReactiveState<T>(func: Function): RequestState<T> {
  const state: RequestState<T> = {
    status: writable('idle'),
    isLoading: writable(false),
    data: writable(null),
    error: writable(null),

    action: async (...args: any[]) => {
      state.status.set('loading');
      state.isLoading.set(true);

      try {
        const data = await func(...args);
        state.data.set(data);
        state.status.set('finished');
      } catch (error: any) {
        state.error.set(error);
        state.status.set('error');
      } finally {
        state.isLoading.set(false);
      }
    },
    reset: () => {
      state.data.set(null);
      state.error.set(null);
      state.status.set('idle');
    },
  };

  return state;
}
