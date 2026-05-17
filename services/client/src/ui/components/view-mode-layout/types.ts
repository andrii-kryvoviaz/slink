import type {
  ColumnDef,
  RowData,
  SortingState,
  Table as TanstackTable,
} from '@tanstack/table-core';

import type { TableSettingsState } from '@slink/lib/settings/composables/useTableSettings.svelte';
import type {
  AbstractPaginatedFeed,
  AppendMode,
  PaginationContext,
} from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';

export interface BaseModeConfig {
  toolbar?: boolean;
  pageSize?: boolean;
  more?: boolean;
  appendMode?: AppendMode;
}

/* eslint-disable @typescript-eslint/no-explicit-any */
export interface TableModeConfig<
  TData extends RowData = any,
> extends BaseModeConfig {
  columns: ColumnDef<TData>[];
  data?: TData[];
  currentPage?: number;
  totalPages?: number;
  initialSorting?: SortingState;
  onPageChange?: (page: number) => void;
  onSortingChange?: (orderBy: string | null, order: 'asc' | 'desc') => void;
}

export type ModeConfig<TData extends RowData = any> =
  | BaseModeConfig
  | TableModeConfig<TData>;

export function isTableMode(config: ModeConfig): config is TableModeConfig {
  return 'columns' in config;
}

export interface ListingContext {
  feed: AbstractPaginatedFeed<any>;
  handlePageSizeChange: (size: number) => Promise<void>;
  tableSettings: TableSettingsState;
  table?: TanstackTable<any>;
  pageSize?: number;
}

export interface ToolbarContext extends ListingContext {
  table?: TanstackTable<any>;
  pageSize?: number;
  pagination: PaginationContext;
}
