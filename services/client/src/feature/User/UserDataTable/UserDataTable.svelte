<script lang="ts">
  import {
    UserActions,
    UserCell,
    UserRoleCell,
    UserStatusCell,
    UserUsernameCell,
  } from '@slink/feature/User';
  import {
    ColumnToggle,
    DataTable,
    PageSizeSelect,
    renderComponent,
    useDataTable,
  } from '@slink/ui/components/data-table';
  import { TablePagination } from '@slink/ui/components/table-pagination';
  import type { ColumnDef } from '@tanstack/table-core';

  import type { User } from '$lib/auth/Type/User';
  import Icon from '@iconify/svelte';

  import type { TableSettingsState } from '@slink/lib/settings/composables/useTableSettings.svelte';

  interface Props {
    users: User[];
    loggedInUser?: User | null;
    onDelete?: (id: string) => void;
    tableSettings: TableSettingsState;
    isLoading?: boolean;
    currentPage?: number;
    totalPages?: number;
    totalItems?: number;
    pageSizeOptions?: number[];
    onPageSizeChange?: (pageSize: number) => void;
    onPageChange?: (page: number) => void;
  }

  let {
    users,
    loggedInUser,
    onDelete,
    tableSettings,
    isLoading = false,
    currentPage = 1,
    totalPages = 1,
    totalItems = 0,
    pageSizeOptions = [12, 24, 48, 96],
    onPageSizeChange,
    onPageChange,
  }: Props = $props();

  const pageSize = $derived(tableSettings.pageSize);
  const columnVisibility = $derived(tableSettings.columnVisibility);

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
      meta: {
        className: 'min-w-[120px]',
      },
      cell: ({ row }) => {
        const user = row.original;
        const isCurrentUser = user.id === loggedInUser?.id;
        return renderComponent(UserStatusCell, { user, isCurrentUser });
      },
    },
    {
      accessorKey: 'roles',
      header: 'Roles',
      meta: {
        className: 'min-w-[100px]',
      },
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

  const { table, setColumnVisibility } = useDataTable({
    data: () => displayUsers,
    columns,
    initialVisibility: {
      displayName: true,
      username: true,
      status: true,
      roles: true,
    },
    currentPage: () => currentPage,
    pageSize: () => pageSize,
    totalPages: () => totalPages,
    onPageChange,
    onColumnVisibilityChange: (visibility) => {
      tableSettings.columnVisibility = visibility;
    },
  });

  let tableContainer: HTMLElement;

  $effect(() => {
    setColumnVisibility(columnVisibility);
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

<div class="w-full flex flex-col gap-6" bind:this={tableContainer}>
  <div
    class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
  >
    <div class="order-2 lg:order-1">
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
      class="order-1 lg:order-2 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end"
    >
      <PageSizeSelect {pageSize} options={pageSizeOptions} {onPageSizeChange} />
      <ColumnToggle {table} />
    </div>
  </div>

  <DataTable {table} {columns} {isLoading}>
    {#snippet emptyState()}
      <div class="flex flex-col items-center gap-3 py-8">
        <div
          class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800"
        >
          <Icon
            icon="heroicons:users"
            class="h-6 w-6 text-slate-400 dark:text-slate-500"
          />
        </div>
        <div class="space-y-1">
          <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
            No users found
          </p>
          <p class="text-xs text-slate-500 dark:text-slate-400">
            Users will appear here once added
          </p>
        </div>
      </div>
    {/snippet}
  </DataTable>
</div>
