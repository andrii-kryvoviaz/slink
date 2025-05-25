import { getContext, hasContext, setContext } from 'svelte';

export const useState = <T>(name: Symbol, state: T): T => {
  if (hasContext(name)) {
    return getContext<T>(name);
  }

  setContext(name, state);

  return state;
};
