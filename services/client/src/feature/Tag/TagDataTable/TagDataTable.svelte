<script lang="ts">
  import {
    TagActionsCell,
    TagCountCell,
    TagNameCell,
    TagsSkeleton,
  } from '@slink/feature/Tag';
  import { Button } from '@slink/ui/components/button';
  import {
    ColumnToggle,
    DataTable,
    PageSizeSelect,
    renderComponent,
    useDataTable,
  } from '@slink/ui/components/data-table';
  import { BaseInput as Input } from '@slink/ui/components/input';
  import { TablePagination } from '@slink/ui/components/table-pagination';
  import type { ColumnDef } from '@tanstack/table-core';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import type { TableSettingsState } from '@slink/lib/settings/composables/useTableSettings.svelte';

  interface Props {
    tags: Tag[];
    onCreate?: () => void;
    onDelete: (tag: Tag) => Promise<void>;
    onMove: (tagId: string, newParentId: string | null) => Promise<void>;
    searchTerm: string;
    onSearchChange: (term: string) => void;
    isLoading?: boolean;
    showSkeleton?: boolean;
    currentPage?: number;
    totalPages?: number;
    totalItems?: number;
    tableSettings: TableSettingsState;
    pageSizeOptions?: number[];
    onPageSizeChange?: (pageSize: number) => void;
    onPageChange?: (page: number) => void;
  }

  let {
    tags,
    onCreate,
    onDelete,
    onMove,
    searchTerm = $bindable(),
    onSearchChange,
    isLoading = false,
    showSkeleton = false,
    currentPage = 1,
    totalPages = 1,
    totalItems = 0,
    tableSettings,
    pageSizeOptions = [10, 20, 50, 100],
    onPageSizeChange,
    onPageChange,
  }: Props = $props();

  const pageSize = $derived(tableSettings.pageSize);
  const columnVisibility = $derived(tableSettings.columnVisibility);

  const columns: ColumnDef<Tag>[] = [
    {
      accessorKey: 'name',
      header: 'Name',
      meta: {
        className: 'sm:w-[300px]',
      },
      cell: ({ row }) => {
        const tag = row.original;
        return renderComponent(TagNameCell, { tag });
      },
    },
    {
      accessorKey: 'imageCount',
      header: 'Images',
      meta: {
        className: 'text-center',
      },
      cell: ({ row }) => {
        const tag = row.original;
        return renderComponent(TagCountCell, {
          count: tag.imageCount,
          type: 'images',
          tag,
        });
      },
    },
    {
      accessorKey: 'children',
      header: 'Children',
      meta: {
        className: 'text-center',
      },
      cell: ({ row }) => {
        const tag = row.original;
        const childrenCount = tag.children?.length || 0;
        return renderComponent(TagCountCell, {
          count: childrenCount,
          type: 'children',
          tag,
        });
      },
    },
    {
      id: 'actions',
      header: 'Actions',
      meta: {
        className: 'text-right',
      },
      enableHiding: false,
      cell: ({ row }) => {
        const tag = row.original;
        return renderComponent(TagActionsCell, {
          tag,
          onDelete,
          onMove,
        });
      },
    },
  ];

  const { table, setColumnVisibility } = useDataTable({
    data: () => tags,
    columns,
    initialVisibility: {
      name: true,
      path: true,
      imageCount: true,
      children: true,
    },
    currentPage: () => currentPage,
    pageSize: () => pageSize,
    totalPages: () => totalPages,
    onPageChange,
    onColumnVisibilityChange: (visibility) => {
      tableSettings.columnVisibility = visibility;
    },
  });

  $effect(() => {
    setColumnVisibility(columnVisibility);
  });
</script>

<div class="w-full flex flex-col gap-6">
  <div
    class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
  >
    <div class="flex-1 lg:max-w-sm relative">
      <div
        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 dark:text-slate-500"
      >
        <Icon icon="lucide:search" class="h-4 w-4" />
      </div>
      <Input
        bind:value={searchTerm}
        placeholder="Search tags..."
        oninput={() => onSearchChange(searchTerm)}
        class="h-9 pl-10 pr-3 text-sm bg-white dark:bg-slate-800/50 border-slate-200/60 dark:border-slate-700/50 focus:border-slate-300 dark:focus:border-slate-600"
      />
    </div>

    <div
      class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
    >
      <div class="order-2 sm:order-1 flex items-center">
        <TablePagination
          currentPageIndex={currentPage - 1}
          {totalPages}
          canPreviousPage={currentPage > 1}
          canNextPage={currentPage < totalPages}
          {totalItems}
          {pageSize}
          loading={isLoading}
          {onPageChange}
        />
      </div>
      <div
        class="order-1 sm:order-2 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end"
      >
        <PageSizeSelect
          {pageSize}
          options={pageSizeOptions}
          {onPageSizeChange}
        />
        <ColumnToggle {table} />
      </div>
    </div>
  </div>

  {#if showSkeleton}
    <TagsSkeleton count={10} />
  {:else}
    <DataTable {table} {isLoading}>
      {#snippet emptyState()}
        <div class="flex flex-col items-center">
          <div
            class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800/60"
          >
            <Icon
              icon="lucide:tag"
              class="h-8 w-8 text-slate-400 dark:text-slate-500"
            />
          </div>
          <div class="mt-5 space-y-1.5 text-center">
            <p class="text-lg font-semibold text-slate-700 dark:text-slate-300">
              No tags found
            </p>
            {#if searchTerm}
              <p class="text-sm text-slate-500 dark:text-slate-400">
                Try adjusting your search term
              </p>
            {:else}
              <p class="text-sm text-slate-500 dark:text-slate-400">
                Create your first tag to get started
              </p>
            {/if}
          </div>
          {#if !searchTerm && onCreate}
            <Button
              variant="glass"
              size="sm"
              rounded="full"
              onclick={onCreate}
              class="mt-4"
            >
              <Icon icon="lucide:plus" class="h-4 w-4" />
              Create Tag
            </Button>
          {/if}
        </div>
      {/snippet}
    </DataTable>
  {/if}
</div>
