import { fromStore } from 'svelte/store';

import { settings } from '@slink/lib/settings';
import type { TableSettings } from '@slink/lib/settings/setters/table';

type TableKey = 'users' | 'tags';

interface TableSettingsInit {
  pageSize: number;
  columnVisibility: Record<string, boolean>;
}

export type TableSettingsState = ReturnType<typeof useTableSettings>;

export function useTableSettings(key: TableKey, initial: TableSettingsInit) {
  const tableSettings = settings.get('table', {
    [key]: initial,
  });

  const pageSize = fromStore(tableSettings[key].pageSize);
  const columnVisibility = fromStore(tableSettings[key].columnVisibility);

  return {
    get pageSize() {
      return pageSize.current ?? initial.pageSize;
    },
    set pageSize(v: number) {
      settings.update('table', {
        [key]: { pageSize: v },
      } as Partial<TableSettings>);
    },
    get columnVisibility() {
      return columnVisibility.current ?? initial.columnVisibility;
    },
    set columnVisibility(v: Record<string, boolean>) {
      settings.update('table', {
        [key]: { columnVisibility: v },
      } as Partial<TableSettings>);
    },
  };
}
