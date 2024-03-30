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
  import FormattedDate from '@slink/components/Common/Text/FormattedDate.svelte';

  let images: ImageListingItem[] = [];
  let meta: ListingMetadata;
  let page = 1;

  const {
    run: fetchImages,
    data: response,
    isLoading,
    status,
  } = ReactiveState<ImageListingResponse>(
    () => {
      return ApiClient.image.getHistory(page++);
    },
    { debounce: 300 }
  );

  $: {
    if ($response) {
      images = [...images, ...$response.data];
      meta = $response.meta;
    }
  }

  $: showLoadMore =
    meta && meta.page < Math.ceil(meta.total / meta.size) && $status !== 'idle';

  $: showPreloader = !images.length && $status !== 'finished';

  $: itemsNotFound = !images.length && $status === 'finished';

  onMount(fetchImages);
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

    <div class="mt-8 flex flex-col gap-6">
      {#each images as image}
        <div
          class="image-container break-inside-avoid rounded-lg border bg-gray-200/10 p-4 backdrop-blur dark:border-gray-800/50"
        >
          <div class="mb-4 flex items-center justify-between">
            <div>
              <Button
                href={`/info/${image.id}`}
                variant="link"
                class="p-0 text-sm font-light opacity-70 hover:opacity-100"
              >
                {image.attributes.fileName}
                <Icon icon="mynaui:external-link" class="ml-1" />
              </Button>
            </div>
            <div class="text-xs">
              <FormattedDate date={image.attributes.createdAt.timestamp} />
            </div>
          </div>
          <div class="flex items-center justify-between text-xs">
            <div class="text-xs">
              {image.metadata.width}x{image.metadata.height} pixels
              <div>
                {image.metadata.mimeType}
              </div>
            </div>
          </div>
        </div>
      {/each}
    </div>

    {#if showLoadMore}
      <div class="mt-8 flex justify-center">
        <Button
          class="w-40"
          size="md"
          variant="secondary"
          loading={$isLoading}
          on:click={fetchImages}
        >
          <span>View More</span>
          <Icon icon="mynaui:chevron-double-right" slot="rightIcon" />
        </Button>
      </div>
    {/if}
  </div>
</section>
