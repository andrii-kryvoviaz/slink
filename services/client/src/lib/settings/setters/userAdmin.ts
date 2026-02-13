import { derived } from 'svelte/store';

import type { Setter } from '@slink/lib/settings/Settings.types';
import type { ViewMode } from '@slink/lib/settings/setters/viewMode';

export type UserAdminSettings = {
  viewMode: ViewMode;
};

export const UserAdminSetter: Setter<'userAdmin', UserAdminSettings> = (
  value,
) => {
  return {
    value,
    viewMode: derived(value, ($value) => $value.viewMode),
  };
};
