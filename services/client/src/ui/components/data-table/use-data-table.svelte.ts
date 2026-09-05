import {
  type ColumnDef,
  type ColumnVisibilityState,
  type PaginationState,
  type RowData,
  type SortingState,
  columnVisibilityFeature,
  createTable,
  metaHelper,
  rowPaginationFeature,
  rowSortingFeature,
  tableFeatures,
} from '@tanstack/svelte-table';

import type { TableSettingsState } from '@slink/lib/settings/composables/useTableSettings.svelte';

interface DataTableColumnMeta {
  className?: string;
  label?: string;
}

const features = tableFeatures({
  rowSortingFeature,
  rowPaginationFeature,
  columnVisibilityFeature,
  columnMeta: metaHelper<DataTableColumnMeta>(),
});

export type DataTableFeatures = typeof features;

interface UseDataTableOptions<TData extends RowData> {
  data: () => TData[];
  columns: ColumnDef<DataTableFeatures, TData>[];
  initialVisibility?: ColumnVisibilityState;
  initialSorting?: SortingState;
  currentPage: () => number;
  pageSize?: () => number;
  totalPages: () => number;
  getRowId?: (row: TData) => string;
  onPageChange?: (page: number) => void;
  onSortingChange?: (orderBy: string | null, order: 'asc' | 'desc') => void;
  onColumnVisibilityChange?: (visibility: ColumnVisibilityState) => void;
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
  let columnVisibility = $state<ColumnVisibilityState>({
    ...(options.initialVisibility ?? {}),
  });

  const table = createTable({
    features,
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

  const setColumnVisibility = (visibility: ColumnVisibilityState) => {
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
