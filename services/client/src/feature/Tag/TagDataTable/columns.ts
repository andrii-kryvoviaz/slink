import { TagActionsCell, TagCountCell, TagNameCell } from '@slink/feature/Tag';
import { renderComponent } from '@slink/ui/components/data-table';
import type { ColumnDef } from '@tanstack/table-core';

import type { Tag } from '@slink/api/Resources/TagResource';

export function createTagColumns(options: {
  onDelete: (tag: Tag) => Promise<void>;
  onMove: (tagId: string, newParentId: string | null) => Promise<void>;
}): ColumnDef<Tag>[] {
  return [
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
          onDelete: options.onDelete,
          onMove: options.onMove,
        });
      },
    },
  ];
}
