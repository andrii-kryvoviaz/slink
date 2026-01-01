<script lang="ts">
  import { LoadMoreButton, StopPropagation } from '@slink/feature/Action';
  import {
    AdminImageDropdown,
    DownloadButton,
    ImagePlaceholder,
    PostViewer,
    ViewCountBadge,
  } from '@slink/feature/Image';
  import BookmarkButton from '@slink/feature/Image/BookmarkButton/BookmarkButton.svelte';
  import { calculateImageCardWeight } from '@slink/feature/Image/utils/calculateImageCardWeight';
  import { Masonry } from '@slink/feature/Layout';
  import { EmptyState } from '@slink/feature/Layout';
  import { ExploreSkeleton } from '@slink/feature/Layout';
  import { ExpandableText, FormattedDate } from '@slink/feature/Text';
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
    publicFeedState.subscribe();
    return () => publicFeedState.unsubscribe();
  });

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
    if (!postViewerState.isOpen && publicFeedState.isDirty) {
      postViewerState.openFromUrlAsync();
    }
  });

  const openPostViewer = (index: number) => {
    postViewerState.open(index);
  };

  const handleImageUpdate = (updatedImage: any) => {
    if (updatedImage.attributes.isPublic) {
      publicFeedState.replaceItem(updatedImage);
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
      <Masonry
        items={publicFeedState.items}
        class="gap-4"
        getItemWeight={calculateImageCardWeight}
      >
        {#snippet itemTemplate(image)}
          {@const index = publicFeedState.items.findIndex(
            (i) => i.id === image.id,
          )}
          <div
            in:fly={{ y: 20, duration: 400, delay: Math.random() * 200 }}
            class="group/card break-inside-avoid rounded-xl overflow-hidden cursor-pointer bg-white dark:bg-gray-900/80 backdrop-blur-sm border border-gray-200/60 dark:border-white/[0.06] hover:border-gray-300 dark:hover:border-white/[0.1] shadow-sm hover:shadow-lg dark:shadow-black/20 dark:hover:shadow-black/40 transition-all duration-300"
            onclick={() => openPostViewer(index)}
            onkeydown={(e) => e.key === 'Enter' && openPostViewer(index)}
            role="button"
            tabindex="0"
          >
            <div class="relative">
              <ImagePlaceholder
                uniqueId={image.id}
                src={`/image/${image.attributes.fileName}`}
                metadata={image.metadata}
                showMetadata={false}
                showOpenInNewTab={false}
                rounded={false}
              />

              <div
                class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover/card:opacity-100 transition-opacity duration-300"
              ></div>

              <div
                class="absolute top-3 left-3 flex items-center gap-2 opacity-0 group-hover/card:opacity-100 transition-all duration-300 translate-y-1 group-hover/card:translate-y-0"
              >
                <ViewCountBadge
                  count={image.attributes.views}
                  variant="overlay"
                />
                <div
                  class="flex items-center gap-1.5 px-2 py-1 rounded-full bg-black/40 backdrop-blur-md text-white text-xs"
                >
                  <Icon icon="ph:frame-corners" class="w-3.5 h-3.5" />
                  <span>{image.metadata.width}×{image.metadata.height}</span>
                </div>
              </div>

              <div
                class="absolute top-3 right-3 flex items-center gap-1.5 opacity-0 group-hover/card:opacity-100 transition-all duration-300 translate-y-1 group-hover/card:translate-y-0"
              >
                <StopPropagation>
                  <DownloadButton
                    imageUrl={`/image/${image.attributes.fileName}`}
                    fileName={image.attributes.fileName}
                    size="sm"
                    variant="overlay"
                  />
                </StopPropagation>
                <StopPropagation>
                  <BookmarkButton
                    imageId={image.id}
                    imageOwnerId={image.owner.id}
                    isBookmarked={image.isBookmarked}
                    bookmarkCount={image.bookmarkCount}
                    size="sm"
                    variant="overlay"
                    onBookmarkChange={(
                      newIsBookmarked: boolean,
                      count: number,
                    ) => {
                      publicFeedState.updateItem(image, {
                        isBookmarked: newIsBookmarked,
                        bookmarkCount: count,
                      });
                    }}
                  />
                </StopPropagation>
              </div>

              <div
                class="absolute inset-0 flex items-center justify-center opacity-0 group-hover/card:opacity-100 transition-all duration-300 pointer-events-none"
              >
                <div
                  class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center transform scale-75 group-hover/card:scale-100 transition-transform duration-300"
                >
                  <Icon
                    icon="ph:arrows-out"
                    class="w-6 h-6 text-white drop-shadow-lg"
                  />
                </div>
              </div>
            </div>

            <div class="p-3">
              <div class="flex items-center gap-2.5">
                <UserAvatar size="sm" user={image.owner} />
                <div class="flex-1 min-w-0">
                  <p
                    class="font-medium text-gray-900 dark:text-gray-100 text-sm leading-tight truncate"
                  >
                    {image.owner.displayName}
                  </p>
                  <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    <FormattedDate
                      date={image.attributes.createdAt.timestamp}
                    />
                  </div>
                </div>
                {#if userIsAdmin}
                  <StopPropagation>
                    <AdminImageDropdown
                      {image}
                      on={{
                        imageUpdate: handleImageUpdate,
                        imageDelete: handleImageDelete,
                      }}
                    />
                  </StopPropagation>
                {/if}
              </div>

              {#if image.attributes.description?.trim()}
                <p
                  class="mt-4 text-sm text-gray-600 dark:text-gray-400 leading-relaxed"
                >
                  <ExpandableText
                    maxLines={2}
                    text={image.attributes.description}
                  />
                </p>
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
