<script lang="ts">
  import { CollectionSkeleton } from '@slink/feature/Layout';
  import { FormattedDate } from '@slink/feature/Text';
  import {
    DataTableLayout,
    renderComponent,
    useDataTable,
  } from '@slink/ui/components/data-table';
  import type { ColumnDef } from '@tanstack/table-core';

  import type { CollectionResponse } from '@slink/api/Response';

  import type { TableSettingsState } from '@slink/lib/settings/composables/useTableSettings.svelte';
  import type { PaginationContext } from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';

  import CollectionActionsCell from './cells/CollectionActionsCell.svelte';
  import CollectionNameCell from './cells/CollectionNameCell.svelte';

  interface Props {
    items: CollectionResponse[];
    isLoading?: boolean;
    showSkeleton?: boolean;
    tableSettings: TableSettingsState;
    pagination: PaginationContext;
    pageSizeOptions?: number[];
    on?: {
      nextPage?: () => void;
      prevPage?: () => void;
      pageSizeChange?: (pageSize: number) => void;
    };
  }

  let {
    items,
    isLoading = false,
    showSkeleton = false,
    tableSettings,
    pagination,
    pageSizeOptions = [12, 24, 48, 96],
    on,
  }: Props = $props();

  const columns: ColumnDef<CollectionResponse>[] = [
    {
      accessorKey: 'name',
      header: () => 'Name',
      meta: {
        className: 'sm:w-[300px]',
      },
      cell: ({ row }) => {
        return renderComponent(CollectionNameCell, {
          collection: row.original,
        });
      },
    },
    {
      accessorKey: 'itemCount',
      header: () => 'Items',
      meta: {
        className: 'text-center',
      },
      cell: ({ row }) => {
        return row.original.itemCount ?? 0;
      },
    },
    {
      accessorKey: 'description',
      header: () => 'Description',
      cell: ({ row }) => {
        const desc = row.original.description;
        if (!desc) return '\u2014';
        return desc.length > 80 ? `${desc.slice(0, 80)}\u2026` : desc;
      },
    },
    {
      accessorKey: 'createdAt',
      header: () => 'Created',
      cell: ({ row }) => {
        return renderComponent(FormattedDate, {
          date: row.original.createdAt.timestamp,
        });
      },
    },
    {
      id: 'actions',
      header: () => 'Actions',
      meta: {
        className: 'text-right',
      },
      enableHiding: false,
      cell: ({ row }) => {
        return renderComponent(CollectionActionsCell, {
          collection: row.original,
        });
      },
    },
  ];

  const { table, pageSize } = useDataTable({
    data: () => items,
    columns,
    currentPage: () => pagination.currentPage,
    totalPages: () => pagination.totalPages,
    tableSettings,
  });
</script>

<DataTableLayout
  {table}
  {pageSize}
  {isLoading}
  {showSkeleton}
  {pageSizeOptions}
  onPageSizeChange={on?.pageSizeChange}
  {pagination}
  onNextPage={on?.nextPage}
  onPrevPage={on?.prevPage}
>
  {#snippet skeleton()}
    <CollectionSkeleton count={12} viewMode="table" />
  {/snippet}
  {#snippet empty()}
    <div class="flex flex-col items-center">
      <div
        class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800/60"
      >
        <svg
          class="h-8 w-8 text-slate-400 dark:text-slate-500"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          stroke-width="1.5"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z"
          />
        </svg>
      </div>
      <div class="mt-5 space-y-1.5 text-center">
        <p class="text-lg font-semibold text-slate-700 dark:text-slate-300">
          No collections found
        </p>
        <p class="text-sm text-slate-500 dark:text-slate-400">
          Create your first collection to get started
        </p>
      </div>
    </div>
  {/snippet}
</DataTableLayout>
