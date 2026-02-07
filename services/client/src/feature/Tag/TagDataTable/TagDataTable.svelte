<script lang="ts">
  import {
    TagActionsCell,
    TagCountCell,
    TagNameCell,
  } from '@slink/feature/Tag';
  import TagSortHeader from '@slink/feature/Tag/TagDataTable/TagSortHeader.svelte';
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
  import type {
    TagSortKey,
    TagSortOrder,
  } from '@slink/feature/Tag/TagDataTable/types';

  import type { TableSettingsState } from '@slink/lib/settings/composables/useTableSettings.svelte';

  interface Props {
    tags: Tag[];
    onDelete: (tag: Tag) => Promise<void>;
    searchTerm: string;
    onSearchChange: (term: string) => void;
    isLoading?: boolean;
    currentPage?: number;
    totalPages?: number;
    totalItems?: number;
    tableSettings: TableSettingsState;
    pageSizeOptions?: number[];
    onPageSizeChange?: (pageSize: number) => void;
    onPageChange?: (page: number) => void;
    sortBy?: TagSortKey | null;
    sortOrder?: TagSortOrder;
    onSortChange?: (sortBy: TagSortKey, sortOrder: TagSortOrder) => void;
  }

  let {
    tags,
    onDelete,
    searchTerm = $bindable(),
    onSearchChange,
    isLoading = false,
    currentPage = 1,
    totalPages = 1,
    totalItems = 0,
    tableSettings,
    pageSizeOptions = [10, 20, 50, 100],
    onPageSizeChange,
    onPageChange,
    sortBy = null,
    sortOrder = 'asc',
    onSortChange,
  }: Props = $props();

  const pageSize = $derived(tableSettings.pageSize);
  const columnVisibility = $derived(tableSettings.columnVisibility);

  const handleSortToggle = (key: TagSortKey) => {
    if (!onSortChange) return;
    const nextOrder: TagSortOrder =
      sortBy !== key ? 'asc' : sortOrder === 'asc' ? 'desc' : 'asc';
    onSortChange(key, nextOrder);
  };

  const sortableHeader = (label: string, sortKey: TagSortKey) => {
    return () =>
      renderComponent(TagSortHeader, {
        label,
        sortKey,
        activeSortKey: sortBy,
        sortOrder,
        onToggle: onSortChange ? handleSortToggle : undefined,
      });
  };

  const columns: ColumnDef<Tag>[] = [
    {
      accessorKey: 'name',
      header: sortableHeader('Name', 'name'),
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
      header: sortableHeader('Images', 'imageCount'),
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
      header: sortableHeader('Children', 'childrenCount'),
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
      <div class="order-2 sm:order-1">
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

  <DataTable {table} {columns} {isLoading}>
    {#snippet loadingState()}
      <div class="flex flex-col items-center gap-3">
        <Icon
          icon="lucide:loader-2"
          class="h-6 w-6 text-slate-400 dark:text-slate-500 animate-spin"
        />
        <p class="text-sm text-slate-500 dark:text-slate-400">
          Loading tags...
        </p>
      </div>
    {/snippet}
    {#snippet emptyState()}
      <div class="flex flex-col items-center gap-3 py-8">
        <div
          class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800"
        >
          <Icon
            icon="lucide:tag"
            class="h-6 w-6 text-slate-400 dark:text-slate-500"
          />
        </div>
        <div class="space-y-1">
          <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
            No tags found
          </p>
          {#if searchTerm}
            <p class="text-xs text-slate-500 dark:text-slate-400">
              Try adjusting your search term
            </p>
          {:else}
            <p class="text-xs text-slate-500 dark:text-slate-400">
              Create your first tag to get started
            </p>
          {/if}
        </div>
      </div>
    {/snippet}
  </DataTable>
</div>
