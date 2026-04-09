<script lang="ts">
  import { LoadMoreButton, StopPropagation } from '@slink/feature/Action';
  import {
    AdminImageDropdown,
    DimensionsBadge,
    DownloadButton,
    ImagePlaceholder,
    LicenseInfo,
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
  import { Button } from '@slink/ui/components/button';
  import { untrack } from 'svelte';

  import { page } from '$app/state';
  import { t } from '$lib/i18n';
  import Icon from '@iconify/svelte';
  import { fade, fly } from 'svelte/transition';

  import type { ImageListingItem } from '@slink/api/Response';

  import { skeleton } from '@slink/lib/actions/skeleton';
  import { isAdmin } from '@slink/lib/auth/utils';
  import { usePostViewerState } from '@slink/lib/state/PostViewerState.svelte';
  import { usePublicImagesFeed } from '@slink/lib/state/PublicImagesFeed.svelte';

  import type { PageServerData } from './$types';

  interface Props {
    data: PageServerData;
  }

  let { data }: Props = $props();

  const userIsAdmin = $derived(isAdmin(data.user));
  const licensingEnabled = $derived(
    data.globalSettings?.image?.enableLicensing ?? false,
  );
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
    } else if (untrack(() => publicFeedState.needsLoad)) {
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

  const handleImageUpdate = (updatedImage: ImageListingItem) => {
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
  <title>{$t('pages.explore.page_title')}</title>
</svelte:head>

<main in:fade={{ duration: 500 }} class="min-h-full">
  <div
    class="container mx-auto px-4 sm:px-6 lg:px-8 py-8"
    use:skeleton={{ feed: publicFeedState }}
  >
    {#if publicFeedState.showSkeleton}
      <div in:fade={{ duration: 200 }}>
        <ExploreSkeleton count={12} />
      </div>
    {:else if publicFeedState.isEmpty}
      <div in:fade={{ duration: 200 }}>
        {#if !publicFeedState.isSearching}
          <EmptyState
            icon="ph:images-duotone"
            title={$t('home.empty.title')}
            description={$t('home.empty.description')}
            variant="blue"
            size="md"
          >
            {#snippet action()}
              <Button
                variant="soft-blue"
                size="md"
                rounded="full"
                href="/upload"
              >
                <Icon icon="ph:upload-simple" class="h-4 w-4" />
                {$t('home.empty.button')}
              </Button>
            {/snippet}
          </EmptyState>
        {:else}
          <EmptyState
            icon="ph:images-duotone"
            title={$t('explore.empty.search.title')}
            description={`${$t('explore.empty.search.description_prefix')} "${publicFeedState.searchTerm}" ${$t('explore.empty.search.description_suffix')}`}
            variant="blue"
            size="md"
          >
            {#snippet action()}
              <Button
                variant="soft-blue"
                size="md"
                rounded="full"
                onclick={() => publicFeedState.resetSearch()}
              >
                <Icon icon="lucide:x" class="h-4 w-4" />
                {$t('explore.empty.search.clear_button')}
              </Button>
            {/snippet}
          </EmptyState>
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
            in:fly={{ y: 20, duration: 300, delay: Math.random() * 100 }}
            class="group break-inside-avoid overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/60 transition-all duration-200 hover:border-gray-300 dark:hover:border-gray-700/80 hover:shadow-md dark:hover:shadow-gray-900/50 cursor-pointer"
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
                class="absolute inset-0 bg-linear-to-t from-black/60 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-200"
              ></div>

              <div
                class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none"
              >
                <div
                  class="w-14 h-14 rounded-full bg-black/50 flex items-center justify-center"
                >
                  <Icon icon="ph:arrows-out" class="w-7 h-7 text-white" />
                </div>
              </div>

              <div class="absolute bottom-2 left-2 flex items-center gap-1.5">
                <ViewCountBadge
                  count={image.attributes.views}
                  variant="overlay"
                />
                <DimensionsBadge
                  width={image.metadata.width}
                  height={image.metadata.height}
                  variant="overlay"
                />
              </div>

              {#if licensingEnabled && image.license}
                <div class="absolute bottom-2 right-2">
                  <LicenseInfo
                    license={image.license}
                    variant="overlay"
                    size="sm"
                  />
                </div>
              {/if}

              <div
                class="absolute top-2 right-2 flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity duration-200"
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
                  class="mt-3 text-sm text-gray-600 dark:text-gray-400 leading-relaxed"
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
          />
        </div>
      {/if}
    {/if}
  </div>
</main>

<PostViewer />
