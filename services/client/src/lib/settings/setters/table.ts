import { derived } from 'svelte/store';

import type { Setter } from '@slink/lib/settings/Settings.types';

export type TableKeySettings = {
  pageSize: number;
  columnVisibility: Record<string, boolean>;
};

export type TableSettings = {
  users: TableKeySettings;
  tags: TableKeySettings;
};

export const TableSetter: Setter<'table', TableSettings> = (value) => {
  return {
    value,
    users: {
      pageSize: derived(value, ($value) => $value.users?.pageSize),
      columnVisibility: derived(
        value,
        ($value) => $value.users?.columnVisibility,
      ),
    },
    tags: {
      pageSize: derived(value, ($value) => $value.tags?.pageSize),
      columnVisibility: derived(
        value,
        ($value) => $value.tags?.columnVisibility,
      ),
    },
  };
};
