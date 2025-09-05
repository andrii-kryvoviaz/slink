<script lang="ts">
  import {
    UserActions,
    UserCell,
    UserRoleCell,
    UserStatusCell,
    UserUsernameCell,
  } from '@slink/feature/User';
  import { Button } from '@slink/ui/components/button';
  import {
    FlexRender,
    createSvelteTable,
    renderComponent,
  } from '@slink/ui/components/data-table';
  import * as DropdownMenu from '@slink/ui/components/dropdown-menu';
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
  import { untrack } from 'svelte';

  import type { User } from '$lib/auth/Type/User';
  import Icon from '@iconify/svelte';

  interface Props {
    users: User[];
    loggedInUser?: User | null;
    onDelete?: (id: string) => void;
    columnVisibility?: Record<string, boolean>;
    onColumnVisibilityChange?: (
      columnVisibility: Record<string, boolean>,
    ) => void;
    isLoading?: boolean;
    currentPage?: number;
    totalPages?: number;
    totalItems?: number;
    pageSize?: number;
    onPageChange?: (page: number) => void;
  }

  let {
    users,
    loggedInUser,
    onDelete,
    columnVisibility: initialColumnVisibility,
    onColumnVisibilityChange,
    isLoading = false,
    currentPage = 1,
    totalPages = 1,
    totalItems = 0,
    pageSize = 10,
    onPageChange,
  }: Props = $props();

  let userUpdates = $state<Record<string, User>>({});

  const handleUserUpdate = (updatedUser: User) => {
    userUpdates[updatedUser.id] = updatedUser;
    userUpdates = { ...userUpdates };
  };

  const displayUsers = $derived(
    users.map((user) => userUpdates[user.id] || user),
  );

  const columns: ColumnDef<User>[] = [
    {
      accessorKey: 'displayName',
      header: 'User',
      cell: ({ row }) => {
        const user = row.original;
        return renderComponent(UserCell, { user });
      },
    },
    {
      accessorKey: 'username',
      header: 'Username',
      cell: ({ row }) => {
        const username = row.getValue('username') as string;
        return renderComponent(UserUsernameCell, { username });
      },
    },
    {
      accessorKey: 'status',
      header: 'Status',
      cell: ({ row }) => {
        const user = row.original;
        const isCurrentUser = user.id === loggedInUser?.id;
        return renderComponent(UserStatusCell, { user, isCurrentUser });
      },
    },
    {
      accessorKey: 'roles',
      header: 'Roles',
      cell: ({ row }) => {
        const user = row.original;
        return renderComponent(UserRoleCell, { user });
      },
    },
    {
      id: 'actions',
      enableHiding: false,
      cell: ({ row }) => {
        const user = row.original;
        return renderComponent(UserActions, {
          user,
          loggedInUser,
          onDelete,
          onUserUpdate: handleUserUpdate,
          variant: 'icon',
          triggerClass:
            'inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-100 h-8 w-8 p-0',
        });
      },
    },
  ];

  let pagination = $state<PaginationState>({
    pageIndex: currentPage - 1,
    pageSize,
  });
  let sorting = $state<SortingState>([]);
  let columnVisibility = $state<VisibilityState>(
    initialColumnVisibility || {
      displayName: true,
      username: true,
      status: true,
      roles: true,
    },
  );

  const table = createSvelteTable({
    get data() {
      return displayUsers;
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
      onColumnVisibilityChange?.(columnVisibility);
    },
  });

  let tableContainer: HTMLElement;

  $effect(() => {
    if (initialColumnVisibility) {
      columnVisibility = initialColumnVisibility;
    }
  });

  $effect(() => {
    pagination = { pageIndex: currentPage - 1, pageSize };
  });

  $effect(() => {
    if (tableContainer) {
      const avatarPlaceholders = tableContainer.querySelectorAll(
        '.avatar-placeholder',
      );
      avatarPlaceholders.forEach((placeholder, index) => {
        const rowIndex = Math.floor(index / 1);
        const userData = table.getRowModel().rows[rowIndex]?.original;
        if (userData) {
          placeholder.className =
            'w-8 h-8 rounded-full flex-shrink-0 bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-sm font-medium';
          placeholder.textContent = userData.displayName
            .charAt(0)
            .toUpperCase();
        }
      });
    }
  });
</script>

<div class="w-full flex flex-col" bind:this={tableContainer}>
  <div class="mb-4 flex items-center justify-between gap-1">
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
            <Icon icon="heroicons:adjustments-horizontal" class="mr-2 size-4" />
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
  <div class="rounded-md border flex-1">
    <Table.Root>
      <Table.Header>
        {#each table.getHeaderGroups() as headerGroup (headerGroup.id)}
          <Table.Row>
            {#each headerGroup.headers as header (header.id)}
              <Table.Head>
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
        {#each table.getRowModel().rows as row (row.id)}
          <Table.Row>
            {#each row.getVisibleCells() as cell (cell.id)}
              <Table.Cell>
                <FlexRender
                  content={cell.column.columnDef.cell}
                  context={cell.getContext()}
                />
              </Table.Cell>
            {/each}
          </Table.Row>
        {:else}
          <Table.Row>
            <Table.Cell colspan={columns.length} class="h-24 text-center">
              No results.
            </Table.Cell>
          </Table.Row>
        {/each}
      </Table.Body>
    </Table.Root>
  </div>
</div>
