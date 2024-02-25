import { type Readable } from 'svelte/store';

import { inMemoryReadable } from '@slink/store/provider/inMemoryReadable';

import { debounce } from '@slink/utils/time/debounce';

type RequestStatus = 'idle' | 'loading' | 'finished' | 'error';

export type RequestState<T> = {
  status: Readable<RequestStatus>;
  isLoading: Readable<boolean>;
  data: Readable<T | null>;
  error: Readable<Error | null>;
  run: (...args: any[]) => Promise<void>;
};

type RequestStateOptions = {
  minExecutionTime?: number;
  debounce?: number;
};

function createDelayPromise(delay: number): Promise<void> {
  return new Promise((resolve) => {
    setTimeout(resolve, delay);
  });
}

export function ReactiveState<T>(
  func: Function,
  options?: RequestStateOptions
): RequestState<T> {
  const [status, setStatus] = inMemoryReadable<RequestStatus>('idle');
  const [isLoading, setIsLoading] = inMemoryReadable<boolean>(false);
  const [data, setData] = inMemoryReadable<T | null>(null);
  const [error, setError] = inMemoryReadable<Error | null>(null);

  function resetState() {
    setData(null);
    setError(null);
    setStatus('idle');
  }

  const mutate = async (...args: any[]) => {
    resetState();

    setStatus('loading');
    setIsLoading(true);

    try {
      const [result] = await Promise.all([
        func(...args),
        createDelayPromise(options?.minExecutionTime ?? 0),
      ]);

      setData(result);
      setStatus('finished');
    } catch (err: any) {
      setError(err);
      setStatus('error');
    } finally {
      setIsLoading(false);
    }
  };

  const debounced = debounce(mutate, options?.debounce ?? 0);
  const run = options?.debounce ? debounced : mutate;

  return {
    status,
    isLoading,
    data,
    error,
    run,
  };
}
