import { derived } from 'svelte/store';

import { SortOrder } from '@slink/lib/enum/SortOrder';
import type { Setter } from '@slink/lib/settings/Settings.types';

export type CommentSettings = {
  sortOrder: SortOrder;
};

export const CommentSetter: Setter<'comment', CommentSettings> = (value) => {
  return {
    value,
    sortOrder: derived(value, ($value) => $value.sortOrder),
  };
};
