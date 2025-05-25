import { browser } from '$app/environment';
import { type Readable, readable } from 'svelte/store';

class InMemoryReadableStore<T> implements Readable<T> {
  private subscriber: ((value: T) => void) | null = null;

  private constructor(private value: T) {}

  public static create<T>(
    defaultValue: T,
  ): [InMemoryReadableStore<T>, (value: T) => void] {
    const store = new InMemoryReadableStore(defaultValue);
    return [store, (value: T) => store.set(value)];
  }

  public subscribe(run: (value: T) => void) {
    this.subscriber = run;
    run(this.value);

    return () => {
      this.subscriber = null;
    };
  }

  private set(value: T) {
    this.value = value;
    if (this.subscriber) {
      this.subscriber(value);
    }
  }
}

export function inMemoryReadable<T>(
  defaultValue: T,
): [Readable<T>, (value: T) => void] {
  if (!browser) {
    return [readable(defaultValue), (value: T) => {}];
  }
  return InMemoryReadableStore.create(defaultValue);
}
