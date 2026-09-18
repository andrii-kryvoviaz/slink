import { page } from '$app/state';

import { defaultSettings } from '@slink/lib/settings/Settings.enums';
import type {
  TableKeySettings,
  TableState,
} from '@slink/lib/settings/UserSettings.svelte';

type TableKey = 'users' | 'tags' | 'history' | 'collections' | 'shares';

interface PageSizeFeed {
  pageSize: number;
  setPageSize(size: number): void;
}

export type TableSettingsState = {
  pageSize: number;
  columnVisibility: Record<string, boolean>;
};

export function useTableSettings(
  key: TableKey | null,
  feed: PageSizeFeed,
): TableSettingsState {
  if (key === null) {
    return {
      get pageSize() {
        return feed.pageSize;
      },
      set pageSize(v: number) {
        feed.setPageSize(v);
      },
      get columnVisibility() {
        return {};
      },
      set columnVisibility(_v: Record<string, boolean>) {},
    };
  }

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
