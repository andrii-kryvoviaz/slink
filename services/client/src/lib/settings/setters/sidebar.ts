import type { Setter } from '@slink/lib/settings/Settings.types';

import { derived } from 'svelte/store';

export type SidebarSettings = {
  expanded: boolean;
};

export const SidebarSetter: Setter<'sidebar', SidebarSettings> = (value) => {
  return {
    value,
    expanded: derived(value, ($value) => $value.expanded),
  };
};
