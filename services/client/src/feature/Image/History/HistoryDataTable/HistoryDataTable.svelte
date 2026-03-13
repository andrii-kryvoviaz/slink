<script lang="ts">
  import { ImageCollectionList } from '@slink/feature/Collection';
  import { ViewCountBadge, VisibilityBadge } from '@slink/feature/Image';
  import { formatMimeType } from '@slink/feature/Image/utils/formatMimeType';
  import { ImageTagList } from '@slink/feature/Tag';
  import { FormattedDate } from '@slink/feature/Text';
  import {
    ColumnToggle,
    DataTable,
    PageSizeSelect,
    renderComponent,
    useDataTable,
  } from '@slink/ui/components/data-table';
  import { TablePagination } from '@slink/ui/components/table-pagination';
  import type { ColumnDef } from '@tanstack/table-core';

  import { bytesToSize } from '$lib/utils/bytesConverter';

  import type { Tag } from '@slink/api/Resources/TagResource';
  import type { ImageListingItem } from '@slink/api/Response';
  import type { CollectionReference } from '@slink/api/Response/Collection/CollectionResponse';

  import type { TableSettingsState } from '@slink/lib/settings/composables/useTableSettings.svelte';
  import type { ImageSelectionState } from '@slink/lib/state/ImageSelectionState.svelte';
  import type { PaginationContext } from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';

  import HistoryActionsCell from './cells/HistoryActionsCell.svelte';
  import HistoryFileNameCell from './cells/HistoryFileNameCell.svelte';
  import HistorySelectCell from './cells/HistorySelectCell.svelte';
  import HistorySelectHeader from './cells/HistorySelectHeader.svelte';
  import HistoryThumbnailCell from './cells/HistoryThumbnailCell.svelte';
  import TagsCollectionsCell from './cells/TagsCollectionsCell.svelte';

  interface Props {
    items: ImageListingItem[];
    selectionState?: ImageSelectionState;
    on?: {
      delete: (id: string) => void;
      collectionChange: (
        imageId: string,
        collections: CollectionReference[],
      ) => void;
      tagChange?: (imageId: string, tags: Tag[]) => void;
      nextPage?: () => void;
      prevPage?: () => void;
      pageSizeChange?: (pageSize: number) => void;
    };
    tableSettings: TableSettingsState;
    isLoading?: boolean;
    pagination: PaginationContext;
    pageSizeOptions?: number[];
  }

  let {
    items,
    selectionState,
    on,
    tableSettings,
    isLoading = false,
    pagination,
    pageSizeOptions = [12, 24, 36, 48],
  }: Props = $props();

  const pageSize = $derived(tableSettings.pageSize);
  const columnVisibility = $derived(tableSettings.columnVisibility);

  const allItemIds = $derived(items.map((item) => item.id));

  const columns: ColumnDef<ImageListingItem>[] = [
    {
      id: 'select',
      header: () => {
        return renderComponent(HistorySelectHeader, {
          get selectionState() {
            return selectionState;
          },
          get allIds() {
            return allItemIds;
          },
        });
      },
      cell: ({ row }) => {
        return renderComponent(HistorySelectCell, {
          itemId: row.original.id,
          selectionState,
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
          onDelete: (id: string) => on?.delete(id),
          onCollectionChange: (
            imageId: string,
            collections: CollectionReference[],
          ) => on?.collectionChange(imageId, collections),
          onTagChange: (imageId: string, tags: Tag[]) =>
            on?.tagChange?.(imageId, tags),
        });
      },
    },
  ];

  const handlePageChange = (page: number) => {
    if (page > pagination.currentPage) {
      on?.nextPage?.();
    } else if (page < pagination.currentPage) {
      on?.prevPage?.();
    }
  };

  const { table, setColumnVisibility } = useDataTable({
    data: () => items,
    columns,
    getRowId: (row) => row.id,
    initialVisibility: {
      fileName: true,
      mimeType: true,
      dimensions: true,
      size: true,
      isPublic: true,
      views: true,
      createdAt: true,
      tagsCollections: true,
    },
    currentPage: () => pagination.currentPage,
    pageSize: () => pageSize,
    totalPages: () => pagination.totalPages,
    onPageChange: (page: number) => handlePageChange(page),
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
    <div class="order-2 lg:order-1 flex items-center">
      <TablePagination
        currentPageIndex={pagination.currentPage - 1}
        totalPages={pagination.totalPages}
        canPreviousPage={pagination.canPrevPage}
        canNextPage={pagination.canNextPage}
        totalItems={pagination.total}
        pageSize={pagination.size}
        loading={isLoading}
        disablePageSelection={true}
        onPageChange={(page) => handlePageChange(page)}
      />
    </div>

    <div
      class="order-1 lg:order-2 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end"
    >
      <PageSizeSelect
        {pageSize}
        options={pageSizeOptions}
        onPageSizeChange={on?.pageSizeChange}
      />
      <ColumnToggle {table} />
    </div>
  </div>

  <DataTable {table} {isLoading} />
</div>
