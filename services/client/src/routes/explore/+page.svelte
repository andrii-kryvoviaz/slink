<script lang="ts">
  import { LoadMoreButton } from '@slink/feature/Action';
  import {
    AdminImageDropdown,
    ImagePlaceholder,
    PostViewer,
  } from '@slink/feature/Image';
  import { Masonry } from '@slink/feature/Layout';
  import { EmptyState } from '@slink/feature/Layout';
  import { ExploreSkeleton } from '@slink/feature/Layout';
  import {
    Badge,
    ExpandableText,
    FormattedDate,
    TextEllipsis,
  } from '@slink/feature/Text';
  import { UserAvatar } from '@slink/feature/User';

  import { page } from '$app/state';
  import Icon from '@iconify/svelte';
  import { fade, fly } from 'svelte/transition';

  import { skeleton } from '@slink/lib/actions/skeleton';
  import { isAdmin } from '@slink/lib/auth/utils';
  import { usePostViewerState } from '@slink/lib/state/PostViewerState.svelte';
  import { usePublicImagesFeed } from '@slink/lib/state/PublicImagesFeed.svelte';

  import type { PageServerData } from './$types';

  interface Props {
    data: PageServerData;
  }

  let { data }: Props = $props();

  const userIsAdmin = isAdmin(data.user);
  const publicFeedState = usePublicImagesFeed();
  const postViewerState = usePostViewerState();
  publicFeedState.reset();
  postViewerState.setFeed(publicFeedState);

  $effect(() => {
    const urlParams = new URLSearchParams(page.url.search);
    const search = urlParams.get('search');
    const searchBy = urlParams.get('searchBy');

    if (search && searchBy) {
      if (
        publicFeedState.searchTerm !== search ||
        publicFeedState.searchBy !== searchBy
      ) {
        publicFeedState.search(search, searchBy);
      }
    } else if (!publicFeedState.isDirty) {
      publicFeedState.load();
    }
  });

  $effect(() => {
    if (publicFeedState.items.length > 0 && !postViewerState.isOpen) {
      postViewerState.openFromUrl();
    }
  });

  const openPostViewer = (index: number) => {
    postViewerState.open(index);
  };

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
    use:skeleton={{
      feed: publicFeedState,
      minDisplayTime: 300,
      showDelay: 100,
    }}
  >
    {#if publicFeedState.showSkeleton}
      <div in:fade={{ duration: 200 }}>
        <ExploreSkeleton count={8} />
      </div>
    {:else if publicFeedState.isEmpty}
      <div in:fade={{ duration: 200 }}>
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
      </div>
    {:else if publicFeedState.items.length > 0}
      <Masonry items={publicFeedState.items} class="gap-6">
        {#snippet itemTemplate(image)}
          {@const index = publicFeedState.items.findIndex(
            (i) => i.id === image.id,
          )}
          <div
            in:fly={{ y: 20, duration: 400, delay: Math.random() * 200 }}
            class="group/card break-inside-avoid bg-white dark:bg-gray-900 rounded-2xl shadow-sm hover:shadow-xl border border-gray-100 dark:border-gray-800 overflow-hidden transition-all duration-300 cursor-pointer"
            onclick={() => openPostViewer(index)}
            onkeydown={(e) => e.key === 'Enter' && openPostViewer(index)}
            role="button"
            tabindex="0"
          >
            <div class="group/header p-5 pb-4">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                  <UserAvatar size="md" user={image.owner} />
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
                {#if userIsAdmin}
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

            <div class="group/image relative">
              <ImagePlaceholder
                uniqueId={image.id}
                src={`/image/${image.attributes.fileName}`}
                metadata={image.metadata}
                showMetadata={false}
                showOpenInNewTab={false}
                rounded={false}
              />
              <div
                class="absolute inset-0 bg-black/0 group-hover/image:bg-black/30 transition-all duration-300 flex items-center justify-center pointer-events-none"
              >
                <div
                  class="opacity-0 group-hover/image:opacity-100 transform scale-90 group-hover/image:scale-100 transition-all duration-300"
                >
                  <Icon
                    icon="heroicons:arrows-pointing-out"
                    class="w-8 h-8 text-white drop-shadow-lg"
                  />
                </div>
              </div>
            </div>

            <div
              class="group/footer flex flex-col justify-between gap-2 py-2 px-5"
            >
              <div
                class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400"
              >
                <div class="flex items-center gap-2">
                  <Badge variant="glass" size="xs">
                    <Icon icon="heroicons:eye" class="w-3 h-3 mr-1" />
                    {image.attributes.views}
                  </Badge>
                  <Badge variant="glass" size="xs">
                    <Icon icon="heroicons:photo" class="w-3 h-3 mr-1" />
                    {image.metadata.width}×{image.metadata.height}
                  </Badge>
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
          </div>
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
          </LoadMoreButton>
        </div>
      {/if}
    {/if}
  </div>
</main>

<PostViewer />
