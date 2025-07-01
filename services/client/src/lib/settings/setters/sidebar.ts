import { derived } from 'svelte/store';

import type { Setter } from '@slink/lib/settings/Settings.types';

export type SidebarSettings = {
  expanded: boolean;
};

export const SidebarSetter: Setter<'sidebar', SidebarSettings> = (value) => {
  return {
    value,
    expanded: derived(value, ($value) => $value.expanded),
  };
};
