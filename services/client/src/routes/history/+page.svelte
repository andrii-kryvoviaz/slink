<script lang="ts">
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { EmptyState } from '@slink/lib/components/UI/EmptyState';
  import { useUploadHistoryFeed } from '@slink/lib/state/UploadHistoryFeed.svelte';

  import { HistoryListView } from '@slink/components/Feature/Image';
  import { LoadMoreButton } from '@slink/components/UI/Action';
  import { Loader } from '@slink/components/UI/Loader';

  const historyFeedState = useUploadHistoryFeed();

  $effect(() => {
    if (!historyFeedState.isDirty) historyFeedState.load();
  });
</script>

<svelte:head>
  <title>Upload History | Slink</title>
</svelte:head>

<section in:fade={{ duration: 300 }}>
  <div class="container mx-auto flex flex-col px-4 py-6 sm:px-6 max-w-6xl">
    {#if historyFeedState.isEmpty}
      <EmptyState
        icon="ph:clock-clockwise-duotone"
        title="No history yet"
        description="Your upload history will appear here. Start uploading images to see your files and manage them easily."
        actionText="Upload Images"
        actionHref="/upload"
        variant="purple"
        size="lg"
      />
    {/if}

    {#if historyFeedState.isLoading}
      <div class="my-8 flex justify-center">
        <div
          class="flex items-center gap-3 px-4 py-3 rounded-full bg-white dark:bg-gray-800/50 border border-gray-200/50 dark:border-gray-700/50 shadow-sm"
        >
          <Loader variant="subtle" size="xs" />
          <span class="text-sm font-medium text-gray-600 dark:text-gray-400"
            >Loading history...</span
          >
        </div>
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
