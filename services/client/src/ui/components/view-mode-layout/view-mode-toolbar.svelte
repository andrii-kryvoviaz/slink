<script lang="ts">
  import {
    ColumnToggle,
    PageSizeSelect,
  } from '@slink/ui/components/data-table';
  import { TablePagination } from '@slink/ui/components/table-pagination';
  import type { Table as TanstackTable } from '@tanstack/table-core';
  import type { Snippet } from 'svelte';

  import type { PaginationContext } from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';

  import type { ToolbarContext } from './types';

  interface Props {
    pagination: PaginationContext;
    isLoading: boolean;
    pageSize: number;
    pageSizeOptions: number[];
    showPageSize: boolean;
    activeTable?: TanstackTable<any>;
    toolbarContext: ToolbarContext;
    toolbar?: Snippet<[ToolbarContext]>;
    onPageSizeChange: (size: number) => void | Promise<void>;
    onPaginationPageChange: (page: number) => void;
  }

  let {
    pagination,
    isLoading,
    pageSize,
    pageSizeOptions,
    showPageSize,
    activeTable,
    toolbarContext,
    toolbar,
    onPageSizeChange,
    onPaginationPageChange,
  }: Props = $props();

  const hasTrailing = $derived(showPageSize || activeTable !== undefined);
</script>

<div class="flex flex-col gap-4 lg:flex-row lg:items-center">
  {#if toolbar}
    <div class="order-2 lg:order-1 flex-1 min-w-0">
      {@render toolbar(toolbarContext)}
    </div>
  {/if}

  {#if activeTable}
    <div class="order-2 lg:order-1 flex items-center gap-4">
      <TablePagination
        currentPageIndex={pagination.currentPage - 1}
        totalPages={pagination.totalPages}
        canPreviousPage={pagination.canPrevPage}
        canNextPage={pagination.canNextPage}
        totalItems={pagination.total}
        pageSize={pagination.size}
        loading={isLoading}
        disablePageSelection={true}
        onPageChange={onPaginationPageChange}
      />
    </div>
  {/if}

  {#if hasTrailing}
    <div
      class="order-1 lg:order-2 lg:ml-auto flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end"
    >
      {#if showPageSize}
        <PageSizeSelect
          {pageSize}
          options={pageSizeOptions}
          {onPageSizeChange}
        />
      {/if}
      {#if activeTable}
        <ColumnToggle table={activeTable} />
      {/if}
    </div>
  {/if}
</div>
