import type { Action } from 'svelte/action';

import { Mode } from '@slink/lib/settings';

export const mode: Action<HTMLElement, string> = (node: HTMLElement) => {
  if (node !== document.documentElement) {
    node = document.documentElement;
  }

  const update = (m: string) => {
    if (!node.classList.contains(m)) {
      node.classList.remove(...Object.values(Mode));
    }

    node.classList.add(m);
  };

  return {
    update,
  };
};
