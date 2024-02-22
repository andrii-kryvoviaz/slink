import { type Readable } from 'svelte/store';

import { inMemoryReadable } from '@slink/store/provider/inMemoryReadable';

type RequestStatus = 'idle' | 'loading' | 'finished' | 'error';

export type RequestState<T> = {
  status: Readable<RequestStatus>;
  isLoading: Readable<boolean>;
  data: Readable<T | null>;
  error: Readable<Error | null>;
  run: (...args: any[]) => Promise<void>;
  reset: () => void;
};

export function ReactiveState<T>(func: Function): RequestState<T> {
  const [status, setStatus] = inMemoryReadable<RequestStatus>('idle');
  const [isLoading, setIsLoading] = inMemoryReadable<boolean>(false);
  const [data, setData] = inMemoryReadable<T | null>(null);
  const [error, setError] = inMemoryReadable<Error | null>(null);

  async function run(...args: any[]): Promise<void> {
    setStatus('loading');
    setIsLoading(true);

    try {
      const result = await func(...args);
      setData(result);
      setStatus('finished');
    } catch (err: any) {
      setError(err);
      setStatus('error');
    } finally {
      setIsLoading(false);
    }
  }

  function reset() {
    setData(null);
    setError(null);
    setStatus('idle');
  }

  return {
    status,
    isLoading,
    data,
    error,
    run,
    reset,
  };
}
