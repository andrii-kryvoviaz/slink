import { FormattedDate } from '@slink/feature/Text';
import { renderComponent } from '@slink/ui/components/data-table';
import type { ColumnDef } from '@tanstack/table-core';

import type { ShareListItemResponse } from '@slink/api/Response/Share/ShareListItemResponse';

import ActionsCell from './cells/ActionsCell.svelte';
import AttributesCell from './cells/AttributesCell.svelte';
import ExpiresCell from './cells/ExpiresCell.svelte';
import ShareableCell from './cells/ShareableCell.svelte';

const toTimestamp = (iso: string): number =>
  Math.floor(new Date(iso).getTime() / 1000);

export function createShareColumns(): ColumnDef<ShareListItemResponse>[] {
  return [
    {
      id: 'shareable',
      header: 'Item',
      cell: ({ row }) =>
        renderComponent(ShareableCell, { share: row.original, size: 'md' }),
    },
    {
      id: 'attributes',
      header: 'Attributes',
      meta: { className: 'w-[256px] @max-md:hidden' },
      cell: ({ row }) =>
        renderComponent(AttributesCell, { share: row.original }),
    },
    {
      id: 'expires',
      header: 'Expires',
      meta: { className: 'w-[110px]' },
      cell: ({ row }) =>
        renderComponent(ExpiresCell, {
          expiresAt: row.original.expiresAt,
          isExpired: row.original.isExpired,
        }),
    },
    {
      accessorKey: 'createdAt',
      header: 'Created',
      meta: { className: 'w-[140px] whitespace-nowrap @max-2xl:hidden' },
      cell: ({ row }) =>
        renderComponent(FormattedDate, {
          date: toTimestamp(row.original.createdAt),
        }),
    },
    {
      id: 'actions',
      header: 'Actions',
      meta: { className: 'text-right w-[64px]' },
      enableHiding: false,
      cell: ({ row }) => renderComponent(ActionsCell, { share: row.original }),
    },
  ];
}

export function shareRowClass(share: ShareListItemResponse): string {
  if (share.isExpired) {
    return '[&>td:not(:last-child)>*]:opacity-60';
  }

  return '';
}
