import { derived } from 'svelte/store';

import type { Setter } from '@slink/lib/settings/Settings.types';

export type NavigationSettings = {
  expandedGroups: Record<string, boolean>;
};

export const NavigationSetter: Setter<'navigation', NavigationSettings> = (
  value,
) => {
  return {
    value,
    expandedGroups: derived(value, ($value) => $value.expandedGroups),
  };
};
