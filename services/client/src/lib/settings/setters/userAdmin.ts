import { derived } from 'svelte/store';

import type { Setter } from '@slink/lib/settings/Settings.types';

export type ViewMode = 'grid' | 'list';

export type UserAdminSettings = {
  viewMode: ViewMode;
  columnVisibility: Record<string, boolean>;
};

export const UserAdminSetter: Setter<'userAdmin', UserAdminSettings> = (
  value,
) => {
  return {
    value,
    viewMode: derived(value, ($value) => $value.viewMode),
    columnVisibility: derived(value, ($value) => $value.columnVisibility),
  };
};
