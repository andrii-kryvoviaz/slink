import { FormattedDate } from '@slink/feature/Text';
import { renderComponent } from '@slink/ui/components/data-table';
import type { ColumnDef } from '@tanstack/table-core';

import type { CollectionResponse } from '@slink/api/Response';

import CollectionActionsCell from './cells/CollectionActionsCell.svelte';
import CollectionNameCell from './cells/CollectionNameCell.svelte';

export function createCollectionColumns(): ColumnDef<CollectionResponse>[] {
  return [
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
}
