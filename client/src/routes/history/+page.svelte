<script lang="ts">
  import type {
    ImageListingItem,
    ImageListingResponse,
    ListingMetadata,
  } from '@slink/api/Response';
  import { onMount } from 'svelte';

  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';

  import { HistoryListView } from '@slink/components/Feature/Image';
  import { Button, LoadMoreButton } from '@slink/components/UI/Action';
  import { Loader } from '@slink/components/UI/Loader';
  import { Heading } from '@slink/components/UI/Text';

  let items: ImageListingItem[] = $state([]);
  let meta: ListingMetadata = $state({
    page: 1,
    size: 12, // good both for 2 and 3 columns
    total: 0,
  });

  const {
    run: fetchImages,
    data: response,
    isLoading,
    status,
  } = ReactiveState<ImageListingResponse>(
    (page: number, limit: number) => {
      return ApiClient.image.getHistory(page, limit);
    },
    { debounce: 300 },
  );

  const onRemove = (id: string) => {
    // reassign the items with the new item list
    items = items.filter((item) => item.id !== id);

    // rerun the fetchImages function with the current page and size
    // in order to have fully filled page with items
    if (items.length > 0) {
      fetchImages(meta.page, meta.size);
    }
  };

  let showLoadMore = $derived(
    meta && meta.page < Math.ceil(meta.total / meta.size) && $status !== 'idle',
  );

  let showPreloader = $derived(!items.length && $status !== 'finished');

  let itemsFound = $derived(items.length);
  let itemsNotFound = $derived(!items.length && $status === 'finished');

  onMount(() => {
    fetchImages(1, meta.size);

    return response.subscribe((value) => {
      if (!value) return;

      // filter out the items that are already in the list
      items = items.concat(
        value.data.filter((item) => !items.some((i) => i.id === item.id)),
      );

      meta = value.meta;
    });
  });
</script>

<svelte:head>
  <title>Upload History | Slink</title>
</svelte:head>

<section in:fade={{ duration: 300 }}>
  <div class="container mx-auto flex flex-col px-6 py-6 sm:py-10">
    {#if itemsFound}
      <Heading>Upload History</Heading>
    {/if}
    {#if itemsNotFound}
      <div
        class="mt-8 flex flex-grow flex-col items-center justify-center font-extralight"
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

    {#if showPreloader}
      <div class="mt-8">
        <Loader>
          <span>Loading history...</span>
        </Loader>
      </div>
    {/if}

    <HistoryListView {items} on={{ delete: onRemove }} />

    <LoadMoreButton
      visible={showLoadMore}
      loading={$isLoading}
      class="mt-8"
      onclick={() => fetchImages(meta.page + 1, meta.size)}
    >
      {#snippet text()}
        <span>View More</span>
      {/snippet}
    </LoadMoreButton>
  </div>
</section>
