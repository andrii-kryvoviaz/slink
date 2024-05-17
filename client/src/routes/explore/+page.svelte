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

  import {
    Button,
    ExpandableText,
    LoadMoreButton,
    TextEllipsis,
  } from '@slink/components/Common';
  import { FormattedDate, Loader } from '@slink/components/Common';
  import { ImagePlaceholder } from '@slink/components/Image';
  import { Heading } from '@slink/components/Layout';
  import { UserAvatar } from '@slink/components/User';

  import type { PageServerData } from './$types';

  export let data: PageServerData;

  let images: ImageListingItem[] = [];
  let meta: ListingMetadata = {
    page: 1,
    size: 10,
    total: 0,
  };

  const resetPage = () => {
    images = [];
    meta = { page: 1, size: 10, total: 0 };
  };

  const {
    run: fetchImages,
    data: response,
    isLoading,
    status,
  } = ReactiveState<ImageListingResponse>(
    (page: number, limit: number) => {
      return ApiClient.image.getPublicImages(page, limit);
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

  $: if (!data.user) {
    resetPage();
    fetchImages();
  }

  onMount(() => fetchImages(1, meta.size));
</script>

<svelte:head>
  <title>Explore Public Images | Slink</title>
</svelte:head>

<section in:fade={{ duration: 300 }}>
  <div class="container mx-auto flex flex-col px-6 py-10">
    <div>
      <Heading>Discover Uploads</Heading>

      <p
        class="mx-auto mt-6 max-w-2xl text-center text-gray-500 dark:text-gray-300"
      >
        A diverse collection of photographs, artwork, and unique visuals
        uploaded by platform users. Explore community's material and share your
        own.
      </p>
    </div>

    {#if showPreloader}
      <div class="mt-8">
        <Loader>
          <span>Loading images...</span>
        </Loader>
      </div>
    {/if}

    {#if itemsNotFound}
      <div
        class="mt-8 flex flex-grow flex-col items-center justify-center font-extralight"
      >
        <div class="my-2 flex flex-col justify-center gap-3">
          {#each Array(4) as _}
            <span
              class="block h-3 w-1 rounded-full bg-gray-400/30 dark:bg-gray-200/10"
            />
          {/each}
        </div>
        <p class="text-[3rem] opacity-70">Oops! Here be nothing yet.</p>
        <p class="text-normal opacity-70">Be the first to upload something.</p>
        <Button class="mt-4" size="md" variant="primary" href="/upload">
          <span>Take me to <b>Upload</b></span>
          <Icon icon="mynaui:chevron-double-right" class="ml-3" />
        </Button>
      </div>
    {/if}

    <div
      class="mt-8 columns-1 gap-5 sm:gap-8 md:columns-2 xl:columns-3 [&>.image-container:not(:first-child)]:mt-8"
    >
      {#each images as image}
        <div
          class="image-container break-inside-avoid rounded-lg border bg-gray-200/10 p-4 backdrop-blur dark:border-gray-800/50"
        >
          <div class="mb-4 flex items-center justify-between">
            <div class="flex items-center">
              <UserAvatar size="xs" user={image.owner} />

              <h1 class="mx-2">
                <TextEllipsis fadeClass="opacity-0" class="text-sm font-light">
                  {image.owner.displayName}
                </TextEllipsis>
              </h1>
            </div>
            <div class="text-xs">
              <FormattedDate date={image.attributes.createdAt.timestamp} />
            </div>
          </div>
          <div class="relative flex justify-center shadow">
            <ImagePlaceholder
              uniqueId={image.id}
              src={`/image/${image.attributes.fileName}?width=400`}
              metadata={image.metadata}
              width={28}
              stretch={true}
              showMetadata={false}
            />
          </div>

          <ExpandableText maxLines={1} text={image.attributes.description} />
        </div>
      {/each}
    </div>

    <LoadMoreButton
      visible={showLoadMore}
      loading={$isLoading}
      class="mt-8"
      on:click={() => fetchImages(meta.page + 1, meta.size)}
    >
      <span slot="text">View More</span>
      <Icon icon="mynaui:chevron-double-right" slot="icon" />
    </LoadMoreButton>
  </div>
</section>
