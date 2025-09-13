import type { Action } from 'svelte/action';

import { Theme } from '@slink/lib/settings';

export const theme: Action<HTMLElement, string> = (node: HTMLElement) => {
  if (node !== document.documentElement) {
    node = document.documentElement;
  }

  const update = (t: string) => {
    if (!node.classList.contains(t)) {
      node.classList.remove(...Object.values(Theme));
    }

    node.classList.add(t);
  };

  return {
    update,
  };
};
