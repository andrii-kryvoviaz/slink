import { page } from '$app/state';

import type {
  TableKeySettings,
  TableState,
} from '@slink/lib/settings/UserSettings.svelte';

type TableKey = 'users' | 'tags';

interface TableSettingsInit {
  pageSize: number;
  columnVisibility: Record<string, boolean>;
}

export type TableSettingsState = ReturnType<typeof useTableSettings>;

export function useTableSettings(key: TableKey, initial: TableSettingsInit) {
  const { settings } = page.data;

  return {
    get pageSize() {
      return settings.table[key]?.pageSize ?? initial.pageSize;
    },
    set pageSize(v: number) {
      settings.updateTable({
        [key]: { pageSize: v },
      } as Partial<Record<keyof TableState, Partial<TableKeySettings>>>);
    },
    get columnVisibility() {
      return settings.table[key]?.columnVisibility ?? initial.columnVisibility;
    },
    set columnVisibility(v: Record<string, boolean>) {
      settings.updateTable({
        [key]: { columnVisibility: v },
      } as Partial<Record<keyof TableState, Partial<TableKeySettings>>>);
    },
  };
}
