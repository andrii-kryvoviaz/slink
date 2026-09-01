import type { Action } from 'svelte/action';

export const theme: Action<HTMLElement, string> = (_, initial) => {
  const root = document.documentElement;

  const update = (name: string) => {
    root.dataset.theme = name;
  };

  update(initial);

  return {
    update,
  };
};
