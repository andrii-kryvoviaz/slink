import { page } from '$app/state';

import type {
  TableKeySettings,
  TableState,
} from '@slink/lib/settings/UserSettings.svelte';
import { defaultSettings } from '@slink/lib/settings/UserSettings.svelte';

type TableKey = 'users' | 'tags' | 'history' | 'collections';

export type TableSettingsState = ReturnType<typeof useTableSettings>;

export function useTableSettings(key: TableKey) {
  const { settings } = page.data;
  const defaults = (defaultSettings.table as TableState)[key];

  return {
    get pageSize() {
      return settings.table[key]?.pageSize ?? defaults.pageSize;
    },
    set pageSize(v: number) {
      settings.updateTable({
        [key]: { pageSize: v },
      } as Partial<Record<keyof TableState, Partial<TableKeySettings>>>);
    },
    get columnVisibility() {
      return settings.table[key]?.columnVisibility ?? defaults.columnVisibility;
    },
    set columnVisibility(v: Record<string, boolean>) {
      settings.updateTable({
        [key]: { columnVisibility: v },
      } as Partial<Record<keyof TableState, Partial<TableKeySettings>>>);
    },
  };
}
