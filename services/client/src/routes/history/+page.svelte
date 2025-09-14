<script lang="ts">
  import { LoadMoreButton } from '@slink/feature/Action';
  import { HistoryListView } from '@slink/feature/Image';
  import { EmptyState } from '@slink/feature/Layout';
  import { HistorySkeleton } from '@slink/feature/Layout';

  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { skeleton } from '@slink/lib/actions/skeleton';
  import { useUploadHistoryFeed } from '@slink/lib/state/UploadHistoryFeed.svelte';

  const historyFeedState = useUploadHistoryFeed();

  $effect(() => {
    if (!historyFeedState.isDirty) historyFeedState.load();
  });
</script>

<svelte:head>
  <title>Upload History | Slink</title>
</svelte:head>

<section in:fade={{ duration: 300 }}>
  <div
    class="container mx-auto flex flex-col px-4 py-6 sm:px-6 max-w-7xl"
    use:skeleton={{
      feed: historyFeedState,
      minDisplayTime: 300,
      showDelay: 200,
    }}
  >
    {#if historyFeedState.showSkeleton}
      <div in:fade={{ duration: 200 }}>
        <HistorySkeleton count={6} />
      </div>
    {:else if historyFeedState.isEmpty}
      <div in:fade={{ duration: 200 }}>
        <EmptyState
          icon="ph:clock-clockwise-duotone"
          title="No history yet"
          description="Your upload history will appear here. Start uploading images to see your files and manage them easily."
          actionText="Upload Images"
          actionHref="/upload"
          variant="purple"
          size="md"
        />
      </div>
    {/if}

    <HistoryListView items={historyFeedState.items} />

    <LoadMoreButton
      class="mt-8"
      visible={historyFeedState.hasMore}
      loading={historyFeedState.isLoading}
      onclick={() =>
        historyFeedState.nextPage({
          debounce: 300,
        })}
      variant="modern"
      rounded="full"
    >
      {#snippet text()}
        <span>View More</span>
      {/snippet}
      {#snippet rightIcon()}
        <Icon
          icon="heroicons:chevron-down"
          class="w-4 h-4 ml-2 transition-transform duration-200"
        />
      {/snippet}
    </LoadMoreButton>
  </div>
</section>
