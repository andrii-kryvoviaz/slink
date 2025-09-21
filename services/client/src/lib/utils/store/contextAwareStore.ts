import { getContext, hasContext, setContext } from 'svelte';

import { readable, writable } from 'svelte/store';
import type { Readable, Writable } from 'svelte/store';

export const useSharedStore = <T, A>(
  name: string,
  fn: (value?: A) => T,
  defaultValue?: A,
): T => {
  if (hasContext(name)) {
    return getContext<T>(name);
  }
  const _value = fn(defaultValue);
  setContext(name, _value);
  return _value;
};

export const useWritable = <T>(name: string, value: T): Writable<T> =>
  useSharedStore<Writable<T>, T>(name, writable, value);

export const useReadable = <T>(name: string, value: T): Readable<T> =>
  useSharedStore<Readable<T>, T>(name, readable, value);
