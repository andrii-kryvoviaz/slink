<script lang="ts">
  import { LoadMoreButton } from '@slink/feature/Action';
  import {
    ExploreGridView,
    ExploreListView,
    PostViewer,
    SavedDateBadge,
  } from '@slink/feature/Image';
  import {
    Card,
    EmptyState,
    ExploreSkeleton,
    GhostPreview,
    PageHeader,
    ViewModeToggle,
    hintIconVariants,
  } from '@slink/feature/Layout';
  import { Button } from '@slink/ui/components/button';
  import { ViewModeLayout } from '@slink/ui/components/view-mode-layout';

  import { page } from '$app/state';
  import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import type { BookmarkItem, ImageListingItem } from '@slink/api/Response';

  import { skeleton } from '@slink/lib/actions/skeleton';
  import { supportedViewModes } from '@slink/lib/settings';
  import { MediaFeedAdapter } from '@slink/lib/state/MediaFeedAdapter';
  import { usePostViewerState } from '@slink/lib/state/PostViewerState.svelte';
  import { useUserBookmarksFeed } from '@slink/lib/state/UserBookmarksFeed.svelte';
  import { messages } from '@slink/lib/utils/i18n/messages/toast.language';

  import type { PageData } from './$types';

  interface Props {
    data: PageData;
  }

  let { data }: Props = $props();

  const { settings } = page.data;

  const licensingEnabled = $derived(
    data.globalSettings?.image?.enableLicensing ?? false,
  );

  const bookmarksFeed = useUserBookmarksFeed();
  const postViewerState = usePostViewerState();
  bookmarksFeed.reset();
  bookmarksFeed.hydrate({ hasItems: data.hasAny });
  postViewerState.setFeed(new MediaFeedAdapter(bookmarksFeed));

  $effect(() => {
    if (!postViewerState.isOpen && bookmarksFeed.isDirty) {
      postViewerState.openFromUrlAsync();
    }
  });

  const items = $derived(bookmarksFeed.items.map((bookmark) => bookmark.image));

  const bookmarkByImageId = $derived(
    new Map<string, BookmarkItem>(
      bookmarksFeed.items.map((bookmark) => [bookmark.image.id, bookmark]),
    ),
  );

  const savedAtTimestamp = (imageId: string): number | undefined =>
    bookmarkByImageId.get(imageId)?.createdAt.timestamp;

  const handleRemoveBookmark = async (imageId: string) => {
    const bookmark = bookmarkByImageId.get(imageId);
    if (!bookmark) return;

    try {
      await bookmarksFeed.removeBookmark(bookmark);
    } catch {
      toast.error(messages.bookmark.failedToUpdate);
    }
  };

  const openPostViewer = (image: ImageListingItem) => {
    const index = bookmarksFeed.getMediaIndex(image.id);
    if (index === -1) return;

    postViewerState.open(index);
  };

  const handleBookmarkChange = (
    image: ImageListingItem,
    isBookmarked: boolean,
    count: number,
  ) => {
    bookmarksFeed.updateItemMedia(image.id, {
      isBookmarked,
      bookmarkCount: count,
    });
  };

  const handleImageUpdate = (updatedImage: ImageListingItem) => {
    bookmarksFeed.updateItemMedia(updatedImage.id, updatedImage);
  };

  const handleImageDelete = async (imageId: string) => {
    const bookmark = bookmarkByImageId.get(imageId);
    if (!bookmark) return;

    await bookmarksFeed.removeItems([bookmark.id]);
  };

  const viewHandlers = {
    open: openPostViewer,
    bookmarkChange: handleBookmarkChange,
    imageUpdate: handleImageUpdate,
    imageDelete: handleImageDelete,
  };
</script>

<svelte:head>
  <title>Bookmarks | Slink</title>
</svelte:head>

{#snippet savedBadge(image: ImageListingItem)}
  {@const savedAt = savedAtTimestamp(image.id)}
  {#if savedAt}
    <SavedDateBadge date={savedAt} variant="overlay" />
  {/if}
{/snippet}

{#snippet unavailableCard(image: Pick<ImageListingItem, 'id'>)}
  <Card class="break-inside-avoid p-8 text-center">
    <Icon icon="ph:image-broken" class="w-12 h-12 mx-auto text-ring mb-3" />
    <p class="text-foreground-muted text-sm">Image no longer available</p>
    <Button
      variant="outline-danger"
      size="sm"
      rounded="lg"
      class="mt-4"
      onclick={() => handleRemoveBookmark(image.id)}
    >
      Remove bookmark
    </Button>
  </Card>
{/snippet}

<main in:fade={{ duration: 500 }} class="min-h-full">
  <div
    class="flex flex-col px-4 py-6 sm:px-6 w-full"
    use:skeleton={{ feed: bookmarksFeed }}
  >
    <PageHeader>
      {#snippet title()}Bookmarks{/snippet}
      {#snippet subtitle()}Your saved images from the community{/snippet}
      {#snippet actions()}
        <ViewModeToggle
          value={settings.bookmarks.viewMode}
          modes={supportedViewModes.bookmarks}
          on={{
            change: (mode) => {
              settings.bookmarks = { viewMode: mode };
            },
          }}
        />
      {/snippet}
    </PageHeader>

    <ViewModeLayout
      feed={bookmarksFeed}
      mode={settings.bookmarks.viewMode}
      config={{
        grid: { toolbar: false, appendMode: 'auto' },
        list: { toolbar: false, appendMode: 'auto' },
      }}
    >
      {#snippet loading(mode)}
        <div in:fade={{ duration: 200 }}>
          <ExploreSkeleton count={12} viewMode={mode} />
        </div>
      {/snippet}
      {#snippet grid()}
        <ExploreGridView
          {items}
          {licensingEnabled}
          userIsAdmin={false}
          badge={savedBadge}
          unavailable={unavailableCard}
          on={viewHandlers}
        />
      {/snippet}
      {#snippet list()}
        <ExploreListView
          {items}
          {licensingEnabled}
          userIsAdmin={false}
          badge={savedBadge}
          unavailable={unavailableCard}
          on={viewHandlers}
        />
      {/snippet}
      {#snippet empty()}
        <div in:fade={{ duration: 200 }}>
          <EmptyState
            kind="first-use"
            title="No bookmarks yet"
            description="Images you bookmark are collected here, ready when you need them."
          >
            {#snippet preview()}
              <GhostPreview mode={settings.bookmarks.viewMode} />
            {/snippet}
            {#snippet action()}
              <Button variant="primary" size="md" rounded="lg" href="/explore">
                <Icon icon="lucide:search" class="h-4 w-4" />
                Explore images
              </Button>
            {/snippet}
            {#snippet hint()}
              <span class={hintIconVariants()}>
                <Icon icon="ph:bookmark-simple" class="h-3 w-3" />
              </span>
              Tap the bookmark on any image to save it here
            {/snippet}
          </EmptyState>
        </div>
      {/snippet}
      {#snippet more()}
        <LoadMoreButton
          class="mt-8"
          visible={bookmarksFeed.hasMore}
          loading={bookmarksFeed.isLoading}
          onclick={() => bookmarksFeed.nextPage({ debounce: 300 })}
          variant="modern"
          rounded="full"
        />
      {/snippet}
    </ViewModeLayout>
  </div>
</main>

<PostViewer />
