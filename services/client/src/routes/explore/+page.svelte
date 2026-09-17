<script lang="ts">
  import { LoadMoreButton } from '@slink/feature/Action';
  import {
    ExploreGridView,
    ExploreListView,
    PostViewer,
  } from '@slink/feature/Image';
  import {
    EmptyState,
    ExploreSkeleton,
    GhostGrid,
    GhostList,
    PageHeader,
    ViewModeToggle,
  } from '@slink/feature/Layout';
  import { Button } from '@slink/ui/components/button';
  import { ViewModeLayout } from '@slink/ui/components/view-mode-layout';

  import { page } from '$app/state';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

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

  const { settings } = page.data;

  const userIsAdmin = $derived(isAdmin(data.user));
  const licensingEnabled = $derived(
    data.globalSettings?.image?.enableLicensing ?? false,
  );
  const publicFeedState = usePublicImagesFeed();
  const postViewerState = usePostViewerState();
  publicFeedState.reset();
  publicFeedState.hydrate({ hasItems: data.hasAny });
  postViewerState.setFeed(publicFeedState);

  $effect(() => {
    publicFeedState.subscribe();
    return () => publicFeedState.unsubscribe();
  });

  const hasSearchInUrl = (): boolean => {
    const params = new URLSearchParams(page.url.search);
    return Boolean(params.get('search') && params.get('searchBy'));
  };

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

  const handleBookmarkChange = (
    image: ImageListingItem,
    isBookmarked: boolean,
    count: number,
  ) => {
    publicFeedState.updateItem(image, {
      isBookmarked,
      bookmarkCount: count,
    });
  };

  const handleImageUpdate = async (updatedImage: ImageListingItem) => {
    if (updatedImage.attributes.isPublic) {
      publicFeedState.replaceItem(updatedImage);
      return;
    }

    await publicFeedState.removeItems([updatedImage.id]);
  };

  const handleImageDelete = async (imageId: string) => {
    await publicFeedState.removeItems([imageId]);
  };

  const viewHandlers = {
    open: openPostViewer,
    bookmarkChange: handleBookmarkChange,
    imageUpdate: handleImageUpdate,
    imageDelete: handleImageDelete,
  };
</script>

<svelte:head>
  <title>Explore Gallery | Slink</title>
</svelte:head>

<main in:fade={{ duration: 500 }} class="min-h-full">
  <div
    class="container mx-auto px-4 sm:px-6 lg:px-8 py-8"
    use:skeleton={{ feed: publicFeedState }}
  >
    <PageHeader>
      {#snippet title()}Explore{/snippet}
      {#snippet subtitle()}Public images from everyone on this instance{/snippet}
      {#snippet actions()}
        <ViewModeToggle
          value={settings.explore.viewMode}
          modes={['grid', 'list']}
          on={{
            change: (mode) => {
              settings.explore = { viewMode: mode };
            },
          }}
        />
      {/snippet}
    </PageHeader>

    <ViewModeLayout
      feed={publicFeedState}
      mode={settings.explore.viewMode}
      onBeforeLoad={hasSearchInUrl}
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
          items={publicFeedState.items}
          {licensingEnabled}
          {userIsAdmin}
          on={viewHandlers}
        />
      {/snippet}
      {#snippet list()}
        <ExploreListView
          items={publicFeedState.items}
          {licensingEnabled}
          {userIsAdmin}
          on={viewHandlers}
        />
      {/snippet}
      {#snippet empty()}
        <div in:fade={{ duration: 200 }}>
          {#if !publicFeedState.isSearching}
            <EmptyState
              kind="first-use"
              title="Nothing shared yet"
              description="Public images from everyone on this instance show up here. Yours could be first."
            >
              {#snippet preview()}
                {#if settings.explore.viewMode === 'list'}
                  <GhostList />
                {:else}
                  <GhostGrid />
                {/if}
              {/snippet}
              {#snippet action()}
                <Button variant="primary" size="md" rounded="lg" href="/upload">
                  <Icon icon="ph:upload-simple" class="h-4 w-4" />
                  Upload an image
                </Button>
              {/snippet}
              {#snippet hint()}
                Uploads are private until you make them public
              {/snippet}
            </EmptyState>
          {:else}
            <EmptyState
              kind="no-results"
              icon="ph:magnifying-glass"
              title="No images found"
              description={`Nothing matches "${publicFeedState.searchTerm}". Try a different search term.`}
            >
              {#snippet action()}
                <Button
                  variant="outline"
                  size="sm"
                  rounded="lg"
                  onclick={() => publicFeedState.resetSearch()}
                >
                  Clear search
                </Button>
              {/snippet}
            </EmptyState>
          {/if}
        </div>
      {/snippet}
      {#snippet more()}
        {#if publicFeedState.hasMore}
          <div class="flex justify-center mt-8">
            <LoadMoreButton
              visible={publicFeedState.hasMore}
              loading={publicFeedState.isLoading}
              onclick={() => publicFeedState.nextPage({ debounce: 300 })}
              variant="modern"
              rounded="full"
            />
          </div>
        {/if}
      {/snippet}
    </ViewModeLayout>
  </div>
</main>

<PostViewer />
