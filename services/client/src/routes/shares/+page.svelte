<script lang="ts">
  import { EmptyState, GhostRows, ShareSkeleton } from '@slink/feature/Layout';
  import {
    SharesFilterBar,
    createShareColumns,
    shareRowClass,
  } from '@slink/feature/Share/MyShares';
  import { Subtitle, Title } from '@slink/feature/Text';
  import { Button } from '@slink/ui/components/button';
  import { DataTable } from '@slink/ui/components/data-table';
  import { ViewModeLayout } from '@slink/ui/components/view-mode-layout';

  import { fade } from 'svelte/transition';

  import { skeleton } from '@slink/lib/actions/skeleton';
  import { provideSharesFeed } from '@slink/lib/state/SharesFeed.svelte';

  import type { PageServerData } from './$types';

  interface Props {
    data: PageServerData;
  }

  let { data }: Props = $props();

  const feed = provideSharesFeed();

  feed.setScope({});
  feed.reset();
  feed.hydrate({ hasItems: data.hasAny });

  const shareColumns = createShareColumns();
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
      pageSizeOptions={[10, 20, 50, 100]}
      config={{
        table: {
          columns: shareColumns,
        },
      }}
    >
      {#snippet table({ table: sharesTable, feed: tableFeed })}
        <DataTable
          table={sharesTable!}
          isLoading={tableFeed.isLoading}
          rowClass={shareRowClass}
        />
      {/snippet}
      {#snippet loading()}
        <ShareSkeleton count={feed.meta.size} />
      {/snippet}
      {#snippet empty()}
        {#if feed.hasActiveFilters}
          <EmptyState
            kind="no-results"
            icon="heroicons:funnel"
            title="No shares match these filters"
            description="Try adjusting your filters or search term."
          >
            {#snippet action()}
              <Button
                variant="outline"
                size="sm"
                rounded="lg"
                onclick={() => feed.resetFilters()}
              >
                Clear filters
              </Button>
            {/snippet}
          </EmptyState>
        {:else}
          <EmptyState
            kind="first-use"
            title="No shares yet"
            description="Publish a link from any image or collection to track it here."
          >
            {#snippet preview()}
              <GhostRows />
            {/snippet}
            {#snippet action()}
              <Button variant="primary" size="md" rounded="lg" href="/history">
                Go to my images
              </Button>
            {/snippet}
          </EmptyState>
        {/if}
      {/snippet}
    </ViewModeLayout>
  </div>
</main>
