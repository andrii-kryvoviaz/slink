<script lang="ts">
  import { page } from '$app/state';
  import Icon from '@iconify/svelte';
  import { fade, fly } from 'svelte/transition';

  import { skeleton } from '@slink/lib/actions/skeleton';
  import { usePublicImagesFeed } from '@slink/lib/state/PublicImagesFeed.svelte';

  import {
    AdminImageDropdown,
    ImagePlaceholder,
  } from '@slink/components/Feature/Image';
  import { UserAvatar } from '@slink/components/Feature/User';
  import { LoadMoreButton } from '@slink/components/UI/Action';
  import { EmptyState } from '@slink/components/UI/EmptyState';
  import { Masonry } from '@slink/components/UI/Layout';
  import { ExploreSkeleton } from '@slink/components/UI/Skeleton';
  import {
    ExpandableText,
    FormattedDate,
    TextEllipsis,
  } from '@slink/components/UI/Text';

  import type { PageServerData } from './$types';

  interface Props {
    data: PageServerData;
  }

  let { data }: Props = $props();

  const isAdmin = data.user?.roles?.includes('ROLE_ADMIN') ?? false;
  const publicFeedState = usePublicImagesFeed();
  publicFeedState.reset();

  $effect(() => {
    const urlParams = new URLSearchParams(page.url.search);
    const search = urlParams.get('search');
    const searchBy = urlParams.get('searchBy');

    if (search && searchBy) {
      publicFeedState.search(search, searchBy);
    } else if (!publicFeedState.isDirty) {
      publicFeedState.load();
    }
  });

  const handleImageUpdate = (updatedImage: any) => {
    if (updatedImage.attributes.isPublic) {
      publicFeedState.updateItem(updatedImage.id, updatedImage);
    } else {
      publicFeedState.removeItem(updatedImage.id);
    }
  };

  const handleImageDelete = (imageId: string) => {
    publicFeedState.removeItem(imageId);
  };
</script>

<svelte:head>
  <title>Explore Gallery | Slink</title>
</svelte:head>

<main in:fade={{ duration: 500 }} class="min-h-full">
  <div
    class="container mx-auto px-4 sm:px-6 lg:px-8 py-8"
    use:skeleton={{ feed: publicFeedState, minDisplayTime: 300 }}
  >
    {#if publicFeedState.showSkeleton}
      <ExploreSkeleton count={8} />
    {/if}

    {#if publicFeedState.isEmpty}
      {#if !publicFeedState.isSearching}
        <EmptyState
          icon="ph:images-duotone"
          title="No images yet"
          description="Be the first to share something amazing with the community. Start by uploading your favorite images."
          actionText="Upload First Image"
          actionHref="/upload"
          variant="blue"
          size="md"
        />
      {:else}
        <EmptyState
          icon="ph:images-duotone"
          title="No images found"
          description={`No images match your search for "${publicFeedState.searchTerm}". Try a different search term or browse all images.`}
          actionText="Clear Search"
          actionClick={() => publicFeedState.resetSearch()}
          variant="blue"
          size="md"
        />
      {/if}
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
                {#if isAdmin}
                  <AdminImageDropdown
                    {image}
                    on={{
                      imageUpdate: handleImageUpdate,
                      imageDelete: handleImageDelete,
                    }}
                  />
                {/if}
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

            <div class="flex flex-col justify-between gap-2 py-2 px-5">
              <div
                class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400"
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

              {#if image.attributes.description?.trim()}
                <div
                  class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed"
                >
                  <ExpandableText
                    maxLines={2}
                    text={image.attributes.description}
                  />
                </div>
              {/if}
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
