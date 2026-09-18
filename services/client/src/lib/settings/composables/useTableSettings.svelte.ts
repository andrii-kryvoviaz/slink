import { page } from '$app/state';

import { defaultSettings } from '@slink/lib/settings/Settings.enums';
import type { TableState } from '@slink/lib/settings/UserSettings.svelte';

export type TableKey = 'users' | 'tags' | 'history' | 'collections' | 'shares';

export type TableSettingsState = {
  pageSize: number;
  columnVisibility: Record<string, boolean>;
};

export function useTableSettings(key: TableKey): TableSettingsState {
  const { settings } = page.data;
  const defaults = (defaultSettings.table as TableState)[key];

  return {
    get pageSize() {
      return settings.table[key]?.pageSize ?? defaults.pageSize;
    },
    set pageSize(v: number) {
      settings.updateTable({ [key]: { pageSize: v } });
    },
    get columnVisibility() {
      return settings.table[key]?.columnVisibility ?? defaults.columnVisibility;
    },
    set columnVisibility(v: Record<string, boolean>) {
      settings.updateTable({ [key]: { columnVisibility: v } });
    },
  };
}
