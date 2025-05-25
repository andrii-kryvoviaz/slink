<script lang="ts">
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { useUploadHistoryFeed } from '@slink/lib/state/UploadHistoryFeed.svelte';

  import { HistoryListView } from '@slink/components/Feature/Image';
  import { Button, LoadMoreButton } from '@slink/components/UI/Action';
  import { Loader } from '@slink/components/UI/Loader';
  import { Heading } from '@slink/components/UI/Text';

  const historyFeedState = useUploadHistoryFeed();

  $effect(() => {
    if (!historyFeedState.isDirty) historyFeedState.load();
  });
</script>

<svelte:head>
  <title>Upload History | Slink</title>
</svelte:head>

<section in:fade={{ duration: 300 }}>
  <div class="container mx-auto flex flex-col px-6 py-6 sm:py-10">
    {#if historyFeedState.hasItems}
      <Heading>Upload History</Heading>
    {/if}
    {#if historyFeedState.isEmpty}
      <div
        class="mt-8 flex grow flex-col items-center justify-center font-extralight"
      >
        <p class="mb-6 text-center text-[3rem] leading-10 opacity-70">
          There’s nothing here yet
        </p>
        <p class="text-normal opacity-70">
          Start uploading to see your history.
        </p>
        <Button class="mt-4" size="md" variant="primary" href="/upload">
          <span>Take me to <b>Upload</b></span>
          <Icon icon="mynaui:chevron-double-right" class="ml-3" />
        </Button>
      </div>
    {/if}

    {#if historyFeedState.isLoading}
      <div class="mt-8">
        <Loader>
          <span>Loading history...</span>
        </Loader>
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
    >
      {#snippet text()}
        <span>View More</span>
      {/snippet}
    </LoadMoreButton>
  </div>
</section>
