<script lang="ts">
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { skeleton } from '@slink/lib/actions/skeleton';
  import { useUploadHistoryFeed } from '@slink/lib/state/UploadHistoryFeed.svelte';

  import { HistoryListView } from '@slink/components/Feature/Image';
  import { LoadMoreButton } from '@slink/components/UI/Action';
  import { EmptyState } from '@slink/components/UI/EmptyState';
  import { HistorySkeleton } from '@slink/components/UI/Skeleton';

  import type { PageData } from './$types';

  interface Props {
    data: PageData;
  }

  let { data }: Props = $props();

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
    class="container mx-auto flex flex-col px-4 py-6 sm:px-6 max-w-6xl"
    use:skeleton={{
      feed: historyFeedState,
      minDisplayTime: 300,
    }}
  >
    {#if historyFeedState.isEmpty}
      <EmptyState
        icon="ph:clock-clockwise-duotone"
        title="No history yet"
        description="Your upload history will appear here. Start uploading images to see your files and manage them easily."
        actionText="Upload Images"
        actionHref="/upload"
        variant="purple"
        size="md"
      />
    {/if}

    {#if historyFeedState.showSkeleton}
      <HistorySkeleton count={6} />
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
