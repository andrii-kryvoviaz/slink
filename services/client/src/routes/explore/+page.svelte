<script lang="ts">
  import Icon from '@iconify/svelte';
  import { fade, fly } from 'svelte/transition';

  import { usePublicImagesFeed } from '@slink/lib/state/PublicImagesFeed.svelte';

  import { ImagePlaceholder } from '@slink/components/Feature/Image';
  import { UserAvatar } from '@slink/components/Feature/User';
  import { Button, LoadMoreButton } from '@slink/components/UI/Action';
  import { Masonry } from '@slink/components/UI/Layout';
  import {
    ExpandableText,
    FormattedDate,
    TextEllipsis,
  } from '@slink/components/UI/Text';

  const publicFeedState = usePublicImagesFeed();
  publicFeedState.reset();

  $effect(() => {
    if (!publicFeedState.isDirty) publicFeedState.load();
  });
</script>

<svelte:head>
  <title>Explore Gallery | Slink</title>
</svelte:head>

<main in:fade={{ duration: 500 }} class="min-h-full">
  <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {#if publicFeedState.isLoading && publicFeedState.items.length === 0}
      <div class="flex justify-center items-center py-20">
        <div class="text-center">
          <div
            class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-50 dark:bg-gray-800/50 mb-4 border border-gray-200/50 dark:border-gray-700/50"
          >
            <div
              class="w-5 h-5 border-2 border-gray-200 dark:border-gray-700 border-t-indigo-500 dark:border-t-indigo-400 rounded-full animate-spin"
            ></div>
          </div>
          <p class="text-gray-500 dark:text-gray-400 text-sm font-normal">
            Loading images...
          </p>
        </div>
      </div>
    {/if}

    {#if publicFeedState.isEmpty}
      <div class="flex flex-col items-center justify-center py-20 text-center">
        <div
          class="w-24 h-24 rounded-full bg-gradient-to-r from-indigo-100 to-purple-100 dark:from-indigo-900/30 dark:to-purple-900/30 flex items-center justify-center mb-8"
        >
          <Icon
            icon="heroicons:photo"
            class="w-12 h-12 text-indigo-600 dark:text-indigo-400"
          />
        </div>
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">
          No images yet
        </h2>
        <p class="text-gray-600 dark:text-gray-400 mb-8 max-w-md">
          Be the first to share something amazing with the community
        </p>
        <Button
          size="lg"
          variant="primary"
          href="/upload"
          class="px-8 py-3 text-base font-medium"
        >
          <Icon icon="heroicons:cloud-arrow-up" class="w-5 h-5 mr-2" />
          Upload First Image
        </Button>
      </div>
    {/if}

    {#if publicFeedState.items.length > 0}
      <Masonry items={publicFeedState.items} class="gap-6">
        {#snippet itemTemplate(image)}
          <article
            in:fly={{ y: 20, duration: 400, delay: Math.random() * 200 }}
            class="group break-inside-avoid bg-white dark:bg-gray-900 rounded-2xl shadow-sm hover:shadow-xl border border-gray-100 dark:border-gray-800 overflow-hidden transition-all duration-300"
          >
            <div class="p-5 pb-4">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                  <UserAvatar size="sm" user={image.owner} />
                  <div class="flex-1 min-w-0">
                    <TextEllipsis
                      class="font-medium text-gray-900 dark:text-white text-sm"
                    >
                      {image.owner.displayName}
                    </TextEllipsis>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                      <FormattedDate
                        date={image.attributes.createdAt.timestamp}
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="relative">
              <ImagePlaceholder
                uniqueId={image.id}
                src={`/image/${image.attributes.fileName}`}
                metadata={image.metadata}
                showMetadata={false}
                rounded={false}
              />
            </div>

            {#if image.attributes.description?.trim()}
              <div class="px-5 pt-3 pb-2">
                <div
                  class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed"
                >
                  <ExpandableText
                    maxLines={2}
                    text={image.attributes.description}
                  />
                </div>
              </div>
            {/if}

            <div
              class="px-5 pb-5 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400"
              class:pt-3={!image.attributes.description?.trim()}
            >
              <div class="flex items-center space-x-4">
                <span class="flex items-center">
                  <Icon icon="heroicons:eye" class="w-4 h-4 mr-1" />
                  {image.attributes.views}
                </span>
                <span class="flex items-center">
                  <Icon icon="heroicons:photo" class="w-4 h-4 mr-1" />
                  {image.metadata.width}×{image.metadata.height}
                </span>
              </div>
            </div>
          </article>
        {/snippet}
      </Masonry>

      {#if publicFeedState.hasMore}
        <div class="flex justify-center mt-12">
          <LoadMoreButton
            visible={publicFeedState.hasMore}
            loading={publicFeedState.isLoading}
            onclick={() => publicFeedState.nextPage({ debounce: 300 })}
            variant="modern"
            rounded="full"
          >
            {#snippet text()}
              <span>Load More Images</span>
            {/snippet}
            {#snippet rightIcon()}
              <Icon
                icon="heroicons:chevron-down"
                class="w-4 h-4 ml-2 transition-transform duration-200"
              />
            {/snippet}
          </LoadMoreButton>
        </div>
      {/if}
    {/if}
  </div>
</main>
