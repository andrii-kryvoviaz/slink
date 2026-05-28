import {
  type ColumnDef,
  type PaginationState,
  type RowData,
  type SortingState,
  type VisibilityState,
  getCoreRowModel,
} from '@tanstack/table-core';

import type { TableSettingsState } from '@slink/lib/settings/composables/useTableSettings.svelte';

import { createSvelteTable } from './data-table.svelte.js';

interface UseDataTableOptions<TData extends RowData> {
  data: () => TData[];
  columns: ColumnDef<TData>[];
  initialVisibility?: VisibilityState;
  initialSorting?: SortingState;
  currentPage: () => number;
  pageSize?: () => number;
  totalPages: () => number;
  getRowId?: (row: TData) => string;
  onPageChange?: (page: number) => void;
  onSortingChange?: (orderBy: string | null, order: 'asc' | 'desc') => void;
  onColumnVisibilityChange?: (visibility: VisibilityState) => void;
  tableSettings?: TableSettingsState;
}

export function useDataTable<TData extends RowData>(
  options: UseDataTableOptions<TData>,
) {
  const resolvedPageSize = () => {
    if (options.tableSettings) {
      return options.tableSettings.pageSize;
    }
    return options.pageSize?.() ?? 20;
  };

  let pagination = $state<PaginationState>({
    pageIndex: 0,
    pageSize: resolvedPageSize(),
  });
  let sorting = $state<SortingState>([...(options.initialSorting ?? [])]);
  let columnVisibility = $state<VisibilityState>({
    ...(options.initialVisibility ?? {}),
  });

  const table = createSvelteTable({
    get data() {
      return options.data();
    },
    columns: options.columns,
    getRowId: options.getRowId ?? ((row: any) => row.id),
    state: {
      get pagination() {
        return pagination;
      },
      get sorting() {
        return sorting;
      },
      get columnVisibility() {
        return columnVisibility;
      },
    },
    getCoreRowModel: getCoreRowModel(),
    manualPagination: true,
    manualSorting: true,
    get pageCount() {
      return options.totalPages();
    },
    onPaginationChange: (updater) => {
      if (typeof updater === 'function') {
        const newPagination = updater(pagination);
        if (
          newPagination.pageIndex !== pagination.pageIndex &&
          options.onPageChange
        ) {
          options.onPageChange(newPagination.pageIndex + 1);
        }
        pagination = newPagination;
      } else {
        if (
          updater.pageIndex !== pagination.pageIndex &&
          options.onPageChange
        ) {
          options.onPageChange(updater.pageIndex + 1);
        }
        pagination = updater;
      }
    },
    onSortingChange: (updater) => {
      if (typeof updater === 'function') {
        sorting = updater(sorting);
      } else {
        sorting = updater;
      }

      if (!options.onSortingChange) {
        return;
      }

      if (sorting.length === 0) {
        options.onSortingChange(null, 'asc');
        return;
      }

      const first = sorting[0];
      if (first.desc) {
        options.onSortingChange(first.id, 'desc');
        return;
      }
      options.onSortingChange(first.id, 'asc');
    },
    onColumnVisibilityChange: (updater) => {
      if (typeof updater === 'function') {
        columnVisibility = updater(columnVisibility);
      } else {
        columnVisibility = updater;
      }

      if (options.tableSettings) {
        options.tableSettings.columnVisibility = columnVisibility;
      } else {
        options.onColumnVisibilityChange?.(columnVisibility);
      }
    },
  });

  $effect(() => {
    pagination = {
      pageIndex: options.currentPage() - 1,
      pageSize: resolvedPageSize(),
    };
  });

  if (options.tableSettings) {
    $effect(() => {
      columnVisibility = { ...options.tableSettings!.columnVisibility };
    });
  }

  const setColumnVisibility = (visibility: VisibilityState) => {
    columnVisibility = visibility;
  };

  return {
    table,
    setColumnVisibility,
    get pageSize() {
      return resolvedPageSize();
    },
  };
}
