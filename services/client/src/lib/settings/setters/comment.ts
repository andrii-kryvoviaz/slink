import { derived } from 'svelte/store';

import type { Setter } from '@slink/lib/settings/Settings.types';

export type CommentSortOrder = 'asc' | 'desc';

export type CommentSettings = {
  sortOrder: CommentSortOrder;
};

export const CommentSetter: Setter<'comment', CommentSettings> = (value) => {
  return {
    value,
    sortOrder: derived(value, ($value) => $value.sortOrder),
  };
};
