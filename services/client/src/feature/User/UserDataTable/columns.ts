import {
  UserActions,
  UserCell,
  UserRoleCell,
  UserStatusCell,
  UserUsernameCell,
} from '@slink/feature/User';
import { renderComponent } from '@slink/ui/components/data-table';
import type { ColumnDef } from '@tanstack/table-core';

import type { User } from '$lib/auth/Type/User';

interface UserColumnCallbacks {
  getLoggedInUser: () => User | null | undefined;
  onDelete?: (id: string) => void;
  onUserUpdate: (user: User) => void;
  t: (key: string) => string;
}

export function createUserColumns(
  callbacks: UserColumnCallbacks,
): ColumnDef<User>[] {
  const tr = callbacks.t;
  return [
    {
      accessorKey: 'displayName',
      header: tr('pages.admin.users.table.user'),
      cell: ({ row }) => {
        const user = row.original;
        return renderComponent(UserCell, { user });
      },
    },
    {
      accessorKey: 'username',
      header: tr('pages.admin.users.table.username'),
      cell: ({ row }) => {
        const username = row.getValue('username') as string;
        return renderComponent(UserUsernameCell, { username });
      },
    },
    {
      accessorKey: 'status',
      header: tr('pages.admin.users.table.status'),
      meta: {
        className: 'min-w-[120px]',
      },
      cell: ({ row }) => {
        const user = row.original;
        const isCurrentUser = user.id === callbacks.getLoggedInUser()?.id;
        return renderComponent(UserStatusCell, { user, isCurrentUser });
      },
    },
    {
      accessorKey: 'roles',
      header: tr('pages.admin.users.table.roles'),
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
      header: tr('pages.admin.users.table.actions'),
      meta: {
        className: 'text-right',
      },
      enableHiding: false,
      cell: ({ row }) => {
        const user = row.original;
        return renderComponent(UserActions, {
          user,
          loggedInUser: callbacks.getLoggedInUser(),
          onDelete: callbacks.onDelete,
          onUserUpdate: callbacks.onUserUpdate,
        });
      },
    },
  ];
}
