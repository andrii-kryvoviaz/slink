import type { Action } from 'svelte/action';

export const theme: Action<HTMLElement, string> = () => {
  const root = document.documentElement;

  return {
    update: (name: string) => {
      root.dataset.theme = name;
    },
  };
};
