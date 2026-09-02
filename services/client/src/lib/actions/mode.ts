import type { Action } from 'svelte/action';

import { Mode } from '@slink/lib/settings';

export const mode: Action<HTMLElement, string> = (_, initial) => {
  const root = document.documentElement;

  const update = (m: string) => {
    root.classList.remove(...Object.values(Mode));
    root.classList.add(m);
  };

  update(initial);

  return {
    update,
  };
};
