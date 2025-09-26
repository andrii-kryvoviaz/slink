<script lang="ts">
  import { Button } from '@slink/ui/components/button';
  import {
    FlexRender,
    createSvelteTable,
    renderComponent,
  } from '@slink/ui/components/data-table';
  import * as DropdownMenu from '@slink/ui/components/dropdown-menu';
  import { Input } from '@slink/ui/components/input';
  import * as Table from '@slink/ui/components/table';
  import { TablePagination } from '@slink/ui/components/table-pagination';
  import {
    type ColumnDef,
    type PaginationState,
    type SortingState,
    type VisibilityState,
    getCoreRowModel,
    getSortedRowModel,
  } from '@tanstack/table-core';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import TagActionsCell from './TagActionsCell.svelte';
  import TagCountCell from './TagCountCell.svelte';
  import TagNameCell from './TagNameCell.svelte';

  interface Props {
    tags: Tag[];
    onDelete: (tag: Tag) => Promise<void>;
    searchTerm: string;
    onSearchChange: (term: string) => void;
    isLoading?: boolean;
    currentPage?: number;
    totalPages?: number;
    totalItems?: number;
    pageSize?: number;
    onPageChange?: (page: number) => void;
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
    pageSize = 20,
    onPageChange,
  }: Props = $props();

  const columns: ColumnDef<Tag>[] = [
    {
      accessorKey: 'name',
      header: 'Name',
      meta: {
        className: 'w-[300px]',
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
        });
      },
    },
  ];

  let pagination = $state<PaginationState>({
    pageIndex: currentPage - 1,
    pageSize,
  });
  let sorting = $state<SortingState>([]);
  let columnVisibility = $state<VisibilityState>({
    name: true,
    path: true,
    imageCount: true,
    children: true,
  });

  const table = createSvelteTable({
    get data() {
      return tags;
    },
    columns,
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
    pageCount: totalPages,
    onPaginationChange: (updater) => {
      if (typeof updater === 'function') {
        const newPagination = updater(pagination);
        if (newPagination.pageIndex !== pagination.pageIndex && onPageChange) {
          onPageChange(newPagination.pageIndex + 1);
        }
        pagination = newPagination;
      } else {
        if (updater.pageIndex !== pagination.pageIndex && onPageChange) {
          onPageChange(updater.pageIndex + 1);
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
    },
  });

  $effect(() => {
    pagination = { pageIndex: currentPage - 1, pageSize };
  });
</script>

<div class="w-full flex flex-col">
  <div class="mb-4 flex items-center justify-between gap-4">
    <div class="flex items-center gap-4">
      <div class="flex-1 max-w-sm">
        <Input
          bind:value={searchTerm}
          placeholder="Search tags..."
          oninput={() => onSearchChange(searchTerm)}
        >
          {#snippet leftIcon()}
            <Icon icon="lucide:search" class="h-4 w-4" />
          {/snippet}
        </Input>
      </div>
    </div>

    <div class="flex items-center gap-4">
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

      <DropdownMenu.Root>
        <DropdownMenu.Trigger>
          {#snippet child({ props })}
            <Button {...props} variant="outline" size="sm">
              <Icon
                icon="heroicons:adjustments-horizontal"
                class="mr-2 size-4"
              />
              Columns
              <Icon icon="heroicons:chevron-down" class="ml-2 size-4" />
            </Button>
          {/snippet}
        </DropdownMenu.Trigger>
        <DropdownMenu.Content align="end">
          {#each table
            .getAllColumns()
            .filter((col) => col.getCanHide()) as column (column)}
            <DropdownMenu.CheckboxItem
              class="capitalize"
              bind:checked={
                () => column.getIsVisible(), (v) => column.toggleVisibility(!!v)
              }
            >
              {column.id}
            </DropdownMenu.CheckboxItem>
          {/each}
        </DropdownMenu.Content>
      </DropdownMenu.Root>
    </div>
  </div>

  <div class="flex-1">
    <Table.Root>
      <Table.Header>
        {#each table.getHeaderGroups() as headerGroup (headerGroup.id)}
          <Table.Row
            class="border-slate-200 dark:border-slate-700 hover:[&,&>svelte-css-wrapper]:[&>th,td]:bg-transparent"
          >
            {#each headerGroup.headers as header (header.id)}
              <Table.Head
                class="{(header.column.columnDef.meta as any)
                  ?.className} bg-slate-100/50 dark:bg-slate-700/30 text-slate-700 dark:text-slate-300 font-semibold"
              >
                {#if !header.isPlaceholder}
                  <FlexRender
                    content={header.column.columnDef.header}
                    context={header.getContext()}
                  />
                {/if}
              </Table.Head>
            {/each}
          </Table.Row>
        {/each}
      </Table.Header>
      <Table.Body>
        {#if isLoading}
          <Table.Row
            class="border-slate-200 dark:border-slate-700 hover:bg-transparent"
          >
            <Table.Cell
              colspan={columns.length}
              class="h-24 text-center text-slate-500 dark:text-slate-400"
            >
              <div class="flex flex-col items-center gap-2">
                <Icon
                  icon="lucide:loader-2"
                  class="h-8 w-8 text-slate-400 dark:text-slate-500 animate-spin"
                />
                <p>Loading tags...</p>
              </div>
            </Table.Cell>
          </Table.Row>
        {:else if table.getRowModel().rows.length > 0}
          {#each table.getRowModel().rows as row (row.id)}
            <Table.Row
              class="border-slate-200 dark:border-slate-700 hover:[&,&>svelte-css-wrapper]:[&>th,td]:bg-slate-100/70 dark:hover:[&,&>svelte-css-wrapper]:[&>th,td]:bg-slate-700/50 transition-all duration-300"
            >
              {#each row.getVisibleCells() as cell (cell.id)}
                <Table.Cell
                  class="{(cell.column.columnDef.meta as any)
                    ?.className} text-slate-700 dark:text-slate-300"
                >
                  <FlexRender
                    content={cell.column.columnDef.cell}
                    context={cell.getContext()}
                  />
                </Table.Cell>
              {/each}
            </Table.Row>
          {/each}
        {:else}
          <Table.Row
            class="border-slate-200 dark:border-slate-700 hover:bg-transparent"
          >
            <Table.Cell
              colspan={columns.length}
              class="h-24 text-center text-slate-500 dark:text-slate-400"
            >
              <div class="flex flex-col items-center gap-2">
                <Icon
                  icon="lucide:tag"
                  class="h-8 w-8 text-slate-300 dark:text-slate-600"
                />
                <p>No tags found</p>
                {#if searchTerm}
                  <p class="text-sm">Try adjusting your search term</p>
                {/if}
              </div>
            </Table.Cell>
          </Table.Row>
        {/if}
      </Table.Body>
    </Table.Root>
  </div>
</div>
