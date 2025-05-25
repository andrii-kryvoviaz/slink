<script lang="ts">
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { usePublicImagesFeed } from '@slink/lib/state/PublicImagesFeed.svelte';

  import { ImagePlaceholder } from '@slink/components/Feature/Image';
  import { UserAvatar } from '@slink/components/Feature/User';
  import { Button, LoadMoreButton } from '@slink/components/UI/Action';
  import { Masonry } from '@slink/components/UI/Layout';
  import { Loader } from '@slink/components/UI/Loader';
  import {
    ExpandableText,
    FormattedDate,
    Heading,
    TextEllipsis,
  } from '@slink/components/UI/Text';

  const publicFeedState = usePublicImagesFeed();
  publicFeedState.reset();

  $effect(() => {
    if (!publicFeedState.isDirty) publicFeedState.load();
  });
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

    {#if publicFeedState.isLoading}
      <div class="mt-8">
        <Loader>
          <span>Loading images...</span>
        </Loader>
      </div>
    {/if}

    {#if publicFeedState.isEmpty}
      <div
        class="mt-8 flex grow flex-col items-center justify-center font-extralight"
      >
        <p class="text-normal opacity-70">Be the first to upload something.</p>
        <Button class="mt-4" size="md" variant="primary" href="/upload">
          <span>Take me to <b>Upload</b></span>
          <Icon icon="mynaui:chevron-double-right" class="ml-3" />
        </Button>
      </div>
    {/if}

    <Masonry items={publicFeedState.items} class="mt-8">
      {#snippet itemTemplate(image)}
        <div
          class="break-inside-avoid rounded-lg border bg-gray-200/10 p-4 dark:border-gray-800/50 max-w-full"
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
          <div class="relative flex justify-center shadow-sm">
            <ImagePlaceholder
              uniqueId={image.id}
              src={`/image/${image.attributes.fileName}`}
              metadata={image.metadata}
              width={40}
              stretch={true}
              showMetadata={false}
            />
          </div>

          <ExpandableText maxLines={1} text={image.attributes.description} />
        </div>
      {/snippet}
    </Masonry>

    <LoadMoreButton
      class="mt-8"
      visible={publicFeedState.hasMore}
      loading={publicFeedState.isLoading}
      onclick={() => publicFeedState.nextPage({ debounce: 300 })}
    >
      {#snippet text()}
        <span>View More</span>
      {/snippet}
    </LoadMoreButton>
  </div>
</section>
