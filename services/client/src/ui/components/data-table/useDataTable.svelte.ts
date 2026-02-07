import {
  type ColumnDef,
  type PaginationState,
  type RowData,
  type SortingState,
  type VisibilityState,
  getCoreRowModel,
  getSortedRowModel,
} from '@tanstack/table-core';

import { createSvelteTable } from './data-table.svelte.js';

interface UseDataTableOptions<TData extends RowData> {
  data: () => TData[];
  columns: ColumnDef<TData>[];
  initialVisibility: VisibilityState;
  currentPage: () => number;
  pageSize: () => number;
  totalPages: () => number;
  onPageChange?: (page: number) => void;
  onColumnVisibilityChange?: (visibility: VisibilityState) => void;
}

export function useDataTable<TData extends RowData>(
  options: UseDataTableOptions<TData>,
) {
  let pagination = $state<PaginationState>({
    pageIndex: 0,
    pageSize: 20,
  });
  let sorting = $state<SortingState>([]);
  let columnVisibility = $state<VisibilityState>({
    ...options.initialVisibility,
  });

  const table = createSvelteTable({
    get data() {
      return options.data();
    },
    columns: options.columns,
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
    getSortedRowModel: getSortedRowModel(),
    manualPagination: true,
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
    },
    onColumnVisibilityChange: (updater) => {
      if (typeof updater === 'function') {
        columnVisibility = updater(columnVisibility);
      } else {
        columnVisibility = updater;
      }
      options.onColumnVisibilityChange?.(columnVisibility);
    },
  });

  $effect(() => {
    pagination = {
      pageIndex: options.currentPage() - 1,
      pageSize: options.pageSize(),
    };
  });

  const setColumnVisibility = (visibility: VisibilityState) => {
    columnVisibility = visibility;
  };

  return {
    table,
    setColumnVisibility,
  };
}
