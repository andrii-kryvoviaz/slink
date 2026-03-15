<script lang="ts">
  import {
    UserActions,
    UserCell,
    UserRoleCell,
    UserStatusCell,
    UserUsernameCell,
    UsersSkeleton,
  } from '@slink/feature/User';
  import {
    DataTableLayout,
    renderComponent,
    useDataTable,
  } from '@slink/ui/components/data-table';
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
    showSkeleton?: boolean;
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
    showSkeleton = false,
    currentPage = 1,
    totalPages = 1,
    totalItems = 0,
    pageSizeOptions = [12, 24, 48, 96],
    onPageSizeChange,
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
      header: 'Actions',
      meta: {
        className: 'text-right',
      },
      enableHiding: false,
      cell: ({ row }) => {
        const user = row.original;
        return renderComponent(UserActions, {
          user,
          loggedInUser,
          onDelete,
          onUserUpdate: handleUserUpdate,
        });
      },
    },
  ];

  const { table, pageSize } = useDataTable({
    data: () => displayUsers,
    columns,
    initialVisibility: {
      displayName: true,
      username: true,
      status: true,
      roles: true,
    },
    currentPage: () => currentPage,
    totalPages: () => totalPages,
    onPageChange,
    tableSettings,
  });
</script>

<DataTableLayout
  {table}
  {pageSize}
  {isLoading}
  {showSkeleton}
  {pageSizeOptions}
  {onPageSizeChange}
  {currentPage}
  {totalPages}
  {totalItems}
  {onPageChange}
>
  {#snippet skeleton()}
    <UsersSkeleton viewMode="list" />
  {/snippet}
  {#snippet empty()}
    <div class="flex flex-col items-center">
      <div
        class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800/60"
      >
        <Icon
          icon="heroicons:users"
          class="h-8 w-8 text-slate-400 dark:text-slate-500"
        />
      </div>
      <div class="mt-5 space-y-1.5 text-center">
        <p class="text-lg font-semibold text-slate-700 dark:text-slate-300">
          No users found
        </p>
        <p class="text-sm text-slate-500 dark:text-slate-400">
          Users will appear here once added
        </p>
      </div>
    </div>
  {/snippet}
</DataTableLayout>
