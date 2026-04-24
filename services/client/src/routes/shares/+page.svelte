<script lang="ts">
  import { EmptyState, ShareSkeleton } from '@slink/feature/Layout';
  import {
    ActionsCell,
    AttributesCell,
    ShareableCell,
    SharesFilterBar,
  } from '@slink/feature/Share/MyShares';
  import { FormattedDate, Subtitle, Title } from '@slink/feature/Text';
  import {
    DataTable,
    DataTableToolbar,
    renderComponent,
  } from '@slink/ui/components/data-table';
  import { ViewModeLayout } from '@slink/ui/components/view-mode-layout';
  import type { ColumnDef } from '@tanstack/table-core';

  import { fade } from 'svelte/transition';

  import type { ShareListItemResponse } from '@slink/api/Response/Share/ShareListItemResponse';

  import { skeleton } from '@slink/lib/actions/skeleton';
  import { provideSharesFeed } from '@slink/lib/state/SharesFeed.svelte';

  const feed = provideSharesFeed();

  feed.setScope({});
  feed.reset();

  const toTimestamp = (iso: string): number =>
    Math.floor(new Date(iso).getTime() / 1000);

  const shareColumns: ColumnDef<ShareListItemResponse>[] = [
    {
      id: 'shareable',
      header: () => 'Item',
      meta: { className: 'sm:w-[320px]' },
      cell: ({ row }) =>
        renderComponent(ShareableCell, { share: row.original, size: 'md' }),
    },
    {
      accessorKey: 'createdAt',
      header: () => 'Created',
      meta: { className: 'w-[160px]' },
      cell: ({ row }) =>
        renderComponent(FormattedDate, {
          date: toTimestamp(row.original.createdAt),
        }),
    },
    {
      id: 'attributes',
      header: () => 'Attributes',
      meta: { className: 'w-[140px]' },
      cell: ({ row }) =>
        renderComponent(AttributesCell, { share: row.original }),
    },
    {
      id: 'actions',
      header: () => 'Actions',
      meta: { className: 'text-right w-[80px]' },
      enableHiding: false,
      cell: ({ row }) => renderComponent(ActionsCell, { share: row.original }),
    },
  ];
</script>

<svelte:head>
  <title>My Shares | Slink</title>
</svelte:head>

<main in:fade={{ duration: 500 }} class="min-h-full">
  <div
    class="flex flex-col gap-5 px-4 py-6 sm:px-6 w-full"
    use:skeleton={{ feed }}
  >
    <header in:fade={{ duration: 400, delay: 100 }} class="min-w-0">
      <Title>My Shares</Title>
      <Subtitle>
        Manage links you have shared from images and collections
      </Subtitle>
    </header>

    <SharesFilterBar {feed} />

    <ViewModeLayout
      {feed}
      mode="table"
      config={{
        table: {
          columns: shareColumns,
        },
      }}
    >
      {#snippet toolbar({
        table,
        pageSize,
        pagination,
        feed: tableFeed,
        handlePageSizeChange,
      })}
        <DataTableToolbar
          {table}
          {pageSize}
          {pagination}
          isLoading={tableFeed.isLoading}
          onPageSizeChange={handlePageSizeChange}
          onNextPage={() => tableFeed.nextPage()}
          onPrevPage={() => tableFeed.prevPage()}
          pageSizeOptions={[10, 20, 50, 100]}
        />
      {/snippet}
      {#snippet table({ table: sharesTable, feed: tableFeed })}
        <DataTable table={sharesTable!} isLoading={tableFeed.isLoading} />
      {/snippet}
      {#snippet loading()}
        <ShareSkeleton count={feed.meta.size} />
      {/snippet}
      {#snippet empty()}
        {#if feed.hasActiveFilters}
          <EmptyState
            icon="heroicons:funnel"
            title="No shares yet"
            description="Try adjusting your filters or search term."
            variant="blue"
            size="md"
          />
        {:else}
          <EmptyState
            icon="ph:paper-plane-tilt-duotone"
            title="No shares yet"
            description="Publish a link from any image or collection to see it here."
            variant="default"
            size="md"
          />
        {/if}
      {/snippet}
    </ViewModeLayout>
  </div>
</main>
