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
    ViewModeToggle,
  } from '@slink/feature/Layout';
  import { SearchBar } from '@slink/feature/Search';
  import { Button } from '@slink/ui/components/button';
  import { ViewModeLayout } from '@slink/ui/components/view-mode-layout';
  import { untrack } from 'svelte';

  import { goto } from '$app/navigation';
  import { page } from '$app/state';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import type { ImageListingItem } from '@slink/api/Response';

  import { skeleton } from '@slink/lib/actions/skeleton';
  import { isAdmin } from '@slink/lib/auth/utils';
  import { supportedViewModes } from '@slink/lib/settings';
  import { usePostViewerState } from '@slink/lib/state/PostViewerState.svelte';
  import { usePublicImagesFeed } from '@slink/lib/state/PublicImagesFeed.svelte';

  import { resolveSearchFilter, urlParamUtils } from '@slink/utils/url';

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

  const urlSearch = $derived(resolveSearchFilter(page.url.searchParams));

  const hasSearchInUrl = (): boolean => urlSearch.searchTerm !== undefined;

  const writeSearchToUrl = (searchTerm: string, searchBy: string) => {
    const term = searchTerm.trim();
    const params = urlParamUtils.create(window.location.href);
    const current = params.toSearchParams();

    if (term) {
      params.set('search', term).set('searchBy', searchBy);
    } else {
      params.delete('search').delete('searchBy');
    }

    const next = params.toSearchParams();
    const isUnchanged =
      (next.get('search') ?? '') === (current.get('search') ?? '') &&
      (next.get('searchBy') ?? '') === (current.get('searchBy') ?? '');

    if (isUnchanged) return;

    goto(params.buildUrl(), {
      replaceState: true,
      keepFocus: true,
      noScroll: true,
    });
  };

  $effect(() => {
    const { searchTerm, searchBy } = urlSearch;
    const feed = untrack(() => ({
      term: publicFeedState.searchTerm,
      by: publicFeedState.searchBy,
      searching: publicFeedState.isSearching,
    }));

    if (searchTerm) {
      if (feed.term !== searchTerm || feed.by !== searchBy) {
        publicFeedState.search(searchTerm, searchBy);
      }
      return;
    }

    writeSearchToUrl('', '');

    if (feed.searching) {
      publicFeedState.resetSearch();
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
    <div class="mb-8 flex items-center gap-3">
      <h1 class="sr-only">Explore</h1>
      <SearchBar
        searchTerm={urlSearch.searchTerm}
        searchBy={urlSearch.searchBy}
        onsearch={({ searchTerm, searchBy }) =>
          writeSearchToUrl(searchTerm, searchBy)}
        onclear={() => writeSearchToUrl('', '')}
      />
      <ViewModeToggle
        value={settings.explore.viewMode}
        modes={supportedViewModes.explore}
        size="xl"
        label="active"
        on={{
          change: (mode) => {
            settings.explore = { viewMode: mode };
          },
        }}
      />
    </div>

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
                  onclick={() => writeSearchToUrl('', '')}
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
