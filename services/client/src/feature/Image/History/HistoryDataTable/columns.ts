import { ImageCollectionList } from '@slink/feature/Collection';
import { ViewCountBadge, VisibilityBadge } from '@slink/feature/Image';
import { formatMimeType } from '@slink/feature/Image/utils/formatMimeType';
import { ImageTagList } from '@slink/feature/Tag';
import { FormattedDate } from '@slink/feature/Text';
import { renderComponent } from '@slink/ui/components/data-table';
import type { ColumnDef } from '@tanstack/table-core';

import { bytesToSize } from '$lib/utils/bytesConverter';

import type { Tag } from '@slink/api/Resources/TagResource';
import type { ImageListingItem } from '@slink/api/Response';
import type { CollectionReference } from '@slink/api/Response/Collection/CollectionResponse';

import type { ImageSelectionState } from '@slink/lib/state/ImageSelectionState.svelte';

import HistoryActionsCell from './cells/HistoryActionsCell.svelte';
import HistoryFileNameCell from './cells/HistoryFileNameCell.svelte';
import HistorySelectCell from './cells/HistorySelectCell.svelte';
import HistorySelectHeader from './cells/HistorySelectHeader.svelte';
import HistoryThumbnailCell from './cells/HistoryThumbnailCell.svelte';
import TagsCollectionsCell from './cells/TagsCollectionsCell.svelte';

interface HistoryColumnCallbacks {
  onDelete: (id: string) => void;
  onCollectionChange: (
    imageId: string,
    collections: CollectionReference[],
  ) => void;
  onTagChange?: (imageId: string, tags: Tag[]) => void;
}

export function createHistoryColumns(
  getSelectionState: () => ImageSelectionState | undefined,
  getAllItemIds: () => string[],
  callbacks: HistoryColumnCallbacks,
): ColumnDef<ImageListingItem>[] {
  return [
    {
      id: 'select',
      header: () => {
        return renderComponent(HistorySelectHeader, {
          get selectionState() {
            return getSelectionState();
          },
          get allIds() {
            return getAllItemIds();
          },
        });
      },
      cell: ({ row }) => {
        return renderComponent(HistorySelectCell, {
          itemId: row.original.id,
          selectionState: getSelectionState(),
        });
      },
      enableHiding: false,
      meta: {
        className: 'w-[40px]',
      },
    },
    {
      id: 'thumbnail',
      header: '',
      enableHiding: false,
      meta: {
        className: 'w-[60px]',
      },
      cell: ({ row }) => {
        return renderComponent(HistoryThumbnailCell, {
          item: row.original,
        });
      },
    },
    {
      accessorKey: 'fileName',
      header: 'File Name',
      accessorFn: (row) => row.attributes.fileName,
      meta: {
        className: 'min-w-[150px]',
      },
      cell: ({ row }) => {
        return renderComponent(HistoryFileNameCell, {
          item: row.original,
        });
      },
    },
    {
      accessorKey: 'mimeType',
      header: 'Type',
      accessorFn: (row) => row.metadata.mimeType,
      meta: {
        className: 'w-[80px]',
      },
      cell: ({ row }) => {
        return formatMimeType(row.original.metadata.mimeType);
      },
    },
    {
      accessorKey: 'dimensions',
      header: 'Dimensions',
      meta: {
        className: 'w-[120px]',
      },
      cell: ({ row }) => {
        return `${row.original.metadata.width}\u00D7${row.original.metadata.height}`;
      },
    },
    {
      accessorKey: 'size',
      header: 'Size',
      accessorFn: (row) => row.metadata.size,
      meta: {
        className: 'w-[90px]',
      },
      cell: ({ row }) => {
        return bytesToSize(row.original.metadata.size);
      },
    },
    {
      accessorKey: 'isPublic',
      header: 'Visibility',
      accessorFn: (row) => row.attributes.isPublic,
      meta: {
        className: 'w-[100px]',
      },
      cell: ({ row }) => {
        return renderComponent(VisibilityBadge, {
          isPublic: row.original.attributes.isPublic,
          variant: 'compact',
        });
      },
    },
    {
      accessorKey: 'views',
      header: 'Views',
      accessorFn: (row) => row.attributes.views,
      meta: {
        className: 'w-[70px] text-center',
      },
      cell: ({ row }) => {
        return renderComponent(ViewCountBadge, {
          count: row.original.attributes.views,
          variant: 'compact',
        });
      },
    },
    {
      accessorKey: 'createdAt',
      header: 'Created',
      accessorFn: (row) => row.attributes.createdAt.timestamp,
      meta: {
        className: 'w-[140px]',
      },
      cell: ({ row }) => {
        return renderComponent(FormattedDate, {
          date: row.original.attributes.createdAt.timestamp,
        });
      },
    },
    {
      id: 'tagsCollections',
      header: 'Tags / Collections',
      meta: {
        className: 'min-w-[150px]',
      },
      cell: ({ row }) => {
        const item = row.original;
        const hasTags = item.tags && item.tags.length > 0;
        const hasCollections = item.collections && item.collections.length > 0;

        if (hasTags && !hasCollections) {
          return renderComponent(ImageTagList, {
            imageId: item.id,
            variant: 'neon',
            showImageCount: false,
            removable: false,
            initialTags: item.tags,
            maxVisible: 3,
            disableHover: true,
          });
        }

        if (!hasTags && hasCollections) {
          return renderComponent(ImageCollectionList, {
            collections: item.collections!,
            maxVisible: 3,
          });
        }

        if (!hasTags && !hasCollections) return '';

        return renderComponent(TagsCollectionsCell, {
          item,
        });
      },
    },
    {
      id: 'actions',
      header: 'Actions',
      enableHiding: false,
      meta: {
        className: 'text-right w-[100px]',
      },
      cell: ({ row }) => {
        return renderComponent(HistoryActionsCell, {
          item: row.original,
          onDelete: (id: string) => callbacks.onDelete(id),
          onCollectionChange: (
            imageId: string,
            collections: CollectionReference[],
          ) => callbacks.onCollectionChange(imageId, collections),
          onTagChange: (imageId: string, tags: Tag[]) =>
            callbacks.onTagChange?.(imageId, tags),
        });
      },
    },
  ];
}
