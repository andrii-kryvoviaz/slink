<script lang="ts">
  import { DataTable, DataTableToolbar } from '@slink/ui/components/data-table';
  import type { Table as TanstackTable } from '@tanstack/table-core';
  import type { Snippet } from 'svelte';

  import type { PaginationContext } from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';

  interface Props {
    table: TanstackTable<any>;
    pageSize: number;
    isLoading?: boolean;
    showSkeleton?: boolean;
    pageSizeOptions?: number[];
    onPageSizeChange?: (size: number) => void;

    currentPage?: number;
    totalPages?: number;
    totalItems?: number;
    onPageChange?: (page: number) => void;

    pagination?: PaginationContext;
    onNextPage?: () => void;
    onPrevPage?: () => void;

    skeleton?: Snippet;
    empty?: Snippet;
    leading?: Snippet;
  }

  let {
    table: dataTable,
    pageSize,
    isLoading = false,
    showSkeleton = false,
    pageSizeOptions = [12, 24, 48, 96],
    onPageSizeChange,

    currentPage,
    totalPages,
    totalItems,
    onPageChange,

    pagination,
    onNextPage,
    onPrevPage,

    skeleton,
    empty,
    leading,
  }: Props = $props();
</script>

<div class="w-full flex flex-col gap-6">
  <DataTableToolbar
    table={dataTable}
    {pageSize}
    {isLoading}
    {pageSizeOptions}
    {onPageSizeChange}
    {currentPage}
    {totalPages}
    {totalItems}
    {onPageChange}
    {pagination}
    {onNextPage}
    {onPrevPage}
    {leading}
  />

  {#if showSkeleton && skeleton}
    {@render skeleton()}
  {:else}
    <DataTable table={dataTable} {isLoading}>
      {#snippet emptyState()}
        {#if empty}{@render empty()}{/if}
      {/snippet}
    </DataTable>
  {/if}
</div>
