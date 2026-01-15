import { derived } from 'svelte/store';
import type { Writable } from 'svelte/store';

import type { Setter } from '@slink/lib/settings/Settings.types';

export type UploadOptionsSettings = {
  expanded: boolean;
};

export const setUploadOptions: Setter<
  'uploadOptions',
  UploadOptionsSettings
> = (value: Writable<UploadOptionsSettings>) => {
  const expanded = derived(value, ($value) => $value.expanded);

  return {
    value,
    expanded,
  };
};
