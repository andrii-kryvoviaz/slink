<script lang="ts">
  import {
    ColumnToggle,
    PageSizeSelect,
  } from '@slink/ui/components/data-table';
  import { TablePagination } from '@slink/ui/components/table-pagination';
  import type { Table as TanstackTable } from '@tanstack/table-core';
  import type { Snippet } from 'svelte';

  import type { PaginationContext } from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';

  interface Props {
    table: TanstackTable<any>;
    pageSize: number;
    isLoading?: boolean;
    pageSizeOptions?: number[];
    onPageSizeChange?: (size: number) => void;

    currentPage?: number;
    totalPages?: number;
    totalItems?: number;
    onPageChange?: (page: number) => void;

    pagination?: PaginationContext;
    onNextPage?: () => void;
    onPrevPage?: () => void;

    leading?: Snippet;
  }

  let {
    table: dataTable,
    pageSize,
    isLoading = false,
    pageSizeOptions = [12, 24, 48, 96],
    onPageSizeChange,

    currentPage,
    totalPages,
    totalItems,
    onPageChange,

    pagination,
    onNextPage,
    onPrevPage,

    leading,
  }: Props = $props();

  const handleCursorPageChange = (page: number) => {
    if (!pagination) return;

    if (onNextPage || onPrevPage) {
      if (page > pagination.currentPage) {
        onNextPage?.();
      } else if (page < pagination.currentPage) {
        onPrevPage?.();
      }
    } else {
      onPageChange?.(page);
    }
  };
</script>

<div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
  {#if leading}
    <div class="order-2 lg:order-1 flex-1 min-w-0">
      {@render leading()}
    </div>
  {/if}

  <div class="order-2 lg:order-1 flex items-center gap-4">
    {#if pagination}
      <TablePagination
        currentPageIndex={pagination.currentPage - 1}
        totalPages={pagination.totalPages}
        canPreviousPage={pagination.canPrevPage}
        canNextPage={pagination.canNextPage}
        totalItems={pagination.total}
        pageSize={pagination.size}
        loading={isLoading}
        disablePageSelection={true}
        onPageChange={handleCursorPageChange}
      />
    {:else}
      <TablePagination
        currentPageIndex={(currentPage ?? 1) - 1}
        totalPages={totalPages ?? 1}
        canPreviousPage={(currentPage ?? 1) > 1}
        canNextPage={(currentPage ?? 1) < (totalPages ?? 1)}
        {totalItems}
        {pageSize}
        loading={isLoading}
        {onPageChange}
      />
    {/if}
  </div>

  <div
    class="order-1 lg:order-2 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end"
  >
    <PageSizeSelect {pageSize} options={pageSizeOptions} {onPageSizeChange} />
    <ColumnToggle table={dataTable} />
  </div>
</div>
