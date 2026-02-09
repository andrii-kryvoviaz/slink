import { debounce } from '@slink/utils/time/debounce';

type RequestStatus = 'idle' | 'loading' | 'finished' | 'error';

export type RequestStateOptions = {
  minExecutionTime?: number;
  debounce?: number;
};

function createDelayPromise(delay: number): Promise<void> {
  return new Promise((resolve) => {
    setTimeout(resolve, delay);
  });
}

export abstract class AbstractHttpState<T> {
  private _status: RequestStatus = $state('idle');
  private _isDirty = $state(false);
  private _error: Error | null = $state(null);

  protected async fetch(
    fetcher: () => Promise<T>,
    setter: (data: T) => void,
    options: RequestStateOptions = {} as RequestStateOptions,
  ) {
    this._error = null;
    this._status = 'loading';

    const { minExecutionTime, debounce: debounceTime } = options;

    const mutateState = async () => {
      try {
        const [result] = await Promise.all([
          fetcher(),
          createDelayPromise(minExecutionTime ?? 0),
        ]);

        setter(result);
        this._status = 'finished';
      } catch (error: unknown) {
        this._error = error instanceof Error ? error : new Error(String(error));
        this._status = 'error';
      } finally {
        this._status = 'idle';
        this._isDirty = true;
      }
    };

    if (debounceTime) {
      return await debounce(mutateState, debounceTime)();
    }

    await mutateState();
  }

  protected markDirty(value: boolean = true) {
    this._isDirty = value;
  }

  get isDirty(): boolean {
    return this._isDirty;
  }

  get needsLoad(): boolean {
    return !this._isDirty && this._status !== 'loading';
  }

  get status(): RequestStatus {
    return this._status;
  }

  get isIdle(): boolean {
    return this._status === 'idle';
  }

  get isLoading(): boolean {
    return this._status === 'loading';
  }

  get isFinished(): boolean {
    return this._status === 'finished';
  }

  get hasError(): boolean {
    return this._error !== null;
  }

  get error(): Error | null {
    return this._error;
  }
}
