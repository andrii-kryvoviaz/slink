<script lang="ts">
  import type { PageServerData } from './$types';
  import type {
    ImageListingItem,
    ImageListingResponse,
    ListingMetadata,
  } from '@slink/api/Response';
  import { onDestroy, onMount } from 'svelte';

  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';

  import { ImagePlaceholder } from '@slink/components/Feature/Image';
  import { UserAvatar } from '@slink/components/Feature/User';
  import { Button, LoadMoreButton } from '@slink/components/UI/Action';
  import { Loader } from '@slink/components/UI/Loader';
  import {
    ExpandableText,
    FormattedDate,
    Heading,
    TextEllipsis,
  } from '@slink/components/UI/Text';

  interface Props {
    data: PageServerData;
  }

  let { data }: Props = $props();

  let images: ImageListingItem[] = $state([]);
  let meta: ListingMetadata = $state({
    page: 1,
    size: 10,
    total: 0,
  });

  const {
    run: fetchImages,
    data: response,
    isLoading,
    status,
  } = ReactiveState<ImageListingResponse>(
    (page: number, limit: number) => {
      return ApiClient.image.getPublicImages(page, limit);
    },
    { debounce: 300 },
  );

  let unsibscribeResponse = response.subscribe((value) => {
    if (value) {
      images = [...images, ...value.data];
      meta = value.meta;
    }
  });

  let showLoadMore = $derived(
    meta && meta.page < Math.ceil(meta.total / meta.size) && $status !== 'idle',
  );
  let showPreloader = $derived(!images.length && $status !== 'finished');
  let itemsNotFound = $derived(!images.length && $status === 'finished');

  onMount(() => fetchImages(1, meta.size));
  onDestroy(unsibscribeResponse);
</script>

<svelte:head>
  <title>Explore Public Images | Slink</title>
</svelte:head>

<section in:fade={{ duration: 300 }}>
  <div class="container mx-auto flex flex-col px-6 py-6 sm:py-10">
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
      onclick={() => fetchImages(meta.page + 1, meta.size)}
    >
      {#snippet text()}
        <span>View More</span>
      {/snippet}
    </LoadMoreButton>
  </div>
</section>
