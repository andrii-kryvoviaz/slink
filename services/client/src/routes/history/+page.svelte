<script lang="ts">
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { useUploadHistoryFeed } from '@slink/lib/state/UploadHistoryFeed.svelte';

  import { HistoryListView } from '@slink/components/Feature/Image';
  import { Button, LoadMoreButton } from '@slink/components/UI/Action';
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
      <div
        class="mt-16 flex grow flex-col items-center justify-center text-center"
      >
        <div class="mb-8 p-6 rounded-full bg-gray-100 dark:bg-gray-800/50">
          <Icon
            icon="ph:clock-clockwise"
            class="w-12 h-12 text-gray-400 dark:text-gray-500"
          />
        </div>
        <h2 class="text-xl font-medium text-gray-900 dark:text-white mb-2">
          No uploads yet
        </h2>
        <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-sm">
          Start uploading images to see your upload history and manage your
          files.
        </p>
        <Button class="px-6 py-3" size="md" variant="primary" href="/upload">
          <Icon icon="ph:cloud-arrow-up" class="w-4 h-4 mr-2" />
          <span>Upload Images</span>
        </Button>
      </div>
    {/if}

    {#if historyFeedState.isLoading}
      <div class="mt-8 flex justify-center">
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
