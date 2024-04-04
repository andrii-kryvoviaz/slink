<script lang="ts">
  import { onMount } from 'svelte';

  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type {
    ImageListingItem,
    ImageListingResponse,
    ListingMetadata,
  } from '@slink/api/Response';

  import { Button, Loader } from '@slink/components/Common';
  import { HistoryListView } from '@slink/components/Layout';

  let items: ImageListingItem[] = [];
  let meta: ListingMetadata = {
    page: 1,
    size: 12, // good both for 2 and 3 columns
    total: 0,
  };

  const {
    run: fetchImages,
    data: response,
    isLoading,
    status,
  } = ReactiveState<ImageListingResponse>(
    (page: number, limit: number) => {
      return ApiClient.image.getHistory(page, limit);
    },
    { debounce: 300 }
  );

  // this has to be a standalone function
  // in order not to trigger reactivity on items change
  const updateListing = (detail: ImageListingItem[]) => {
    // filter out the items that are already in the list
    const newItems = detail.filter(
      (item) => !items.some((i) => i.id === item.id)
    );
    items = items.concat(newItems);
  };

  $: if ($response) {
    updateListing($response.data);
    meta = $response.meta;
  }

  const onUpdate = ({ detail }: { detail: ImageListingItem[] }) => {
    // reassign the items with the new item list
    items = detail;

    // rerun the fetchImages function with the current page and size
    // in order to have fully filled page with items
    if (items.length > 0) {
      fetchImages(meta.page, meta.size);
    }
  };

  $: showLoadMore =
    meta && meta.page < Math.ceil(meta.total / meta.size) && $status !== 'idle';

  $: showPreloader = !items.length && $status !== 'finished';

  $: itemsNotFound = !items.length && $status === 'finished';

  onMount(() => fetchImages(1, meta.size));
</script>

<svelte:head>
  <title>Upload History | Slink</title>
</svelte:head>

<section in:fade={{ duration: 300 }}>
  <div class="container mx-auto flex flex-col px-6 py-10">
    {#if !itemsNotFound}
      <div>
        <h1
          class="text-center text-2xl font-semibold capitalize text-gray-800 dark:text-white lg:text-3xl"
        >
          Upload History
        </h1>

        <div class="mx-auto mt-6 flex justify-center">
          <span class="inline-block h-1 w-40 rounded-full bg-indigo-500" />
          <span class="mx-1 inline-block h-1 w-3 rounded-full bg-indigo-500" />
          <span class="inline-block h-1 w-1 rounded-full bg-indigo-500" />
        </div>
      </div>
    {:else}
      <div
        class="mt-8 flex flex-grow flex-col items-center justify-center font-extralight"
      >
        <p class="text-[3rem] opacity-70">Oops! Here be nothing yet.</p>
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

    <HistoryListView {items} on:updateListing={onUpdate} />

    {#if showLoadMore}
      <div class="mt-8 flex justify-center">
        <Button
          class="w-40"
          size="md"
          variant="secondary"
          loading={$isLoading}
          on:click={() => fetchImages(meta.page + 1, meta.size)}
        >
          <span>View More</span>
          <Icon icon="mynaui:chevron-double-right" slot="rightIcon" />
        </Button>
      </div>
    {/if}
  </div>
</section>
