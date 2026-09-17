<script lang="ts">
  import { LoadMoreButton } from '@slink/feature/Action';
  import {
    CollectionGridView,
    CreateCollectionDialog,
  } from '@slink/feature/Collection';
  import { createCollectionColumns } from '@slink/feature/Collection/CollectionViews/CollectionDataTable/columns.svelte';
  import {
    CollectionSkeleton,
    EmptyState,
    GhostFolders,
    PageHeader,
    ViewModeToggle,
  } from '@slink/feature/Layout';
  import { Button } from '@slink/ui/components/button';
  import { DataTable } from '@slink/ui/components/data-table';
  import { EnhancedInput } from '@slink/ui/components/input';
  import { SplitButton } from '@slink/ui/components/split-button';
  import { ViewModeLayout } from '@slink/ui/components/view-mode-layout';

  import { page } from '$app/state';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { skeleton } from '@slink/lib/actions/skeleton';
  import { useCollectionListFeed } from '@slink/lib/state/CollectionListFeed.svelte';
  import { createCreateCollectionModalState } from '@slink/lib/state/CreateCollectionModalState.svelte';

  import type { PageServerData } from './$types';

  interface Props {
    data: PageServerData;
  }

  let { data }: Props = $props();

  const { settings } = page.data;

  const collectionsFeed = useCollectionListFeed();
  collectionsFeed.reset();
  collectionsFeed.hydrate({ hasItems: data.hasAny });

  const createModalState = createCreateCollectionModalState();

  function handleCreateCollection() {
    createModalState.open(() => {
      collectionsFeed.reload();
    });
  }
</script>

<svelte:head>
  <title>Collections | Slink</title>
</svelte:head>

<main in:fade={{ duration: 500 }} class="min-h-full">
  <div
    class="flex flex-col px-4 py-6 sm:px-6 w-full"
    use:skeleton={{ feed: collectionsFeed }}
  >
    <div in:fade={{ duration: 400, delay: 100 }}>
      <PageHeader>
        {#snippet title()}Collections{/snippet}
        {#snippet subtitle()}Organize your images into albums{/snippet}
        {#snippet actions()}
          <ViewModeToggle
            value={settings.collections.viewMode}
            modes={['grid', 'table']}
            on={{
              change: (mode) => {
                settings.collections = {
                  ...settings.collections,
                  viewMode: mode,
                };
              },
            }}
          />
          <SplitButton onclick={handleCreateCollection}>
            Create
            {#snippet aside()}
              <Icon icon="lucide:plus" class="w-3.5 h-3.5" />
            {/snippet}
          </SplitButton>
        {/snippet}
      </PageHeader>
    </div>

    <ViewModeLayout
      feed={collectionsFeed}
      mode={settings.collections.viewMode}
      config={{
        table: {
          columns: createCollectionColumns(),
        },
      }}
    >
      {#snippet toolbar()}
        <div class="lg:max-w-sm">
          <EnhancedInput
            debounce={300}
            oninput={(e) => (collectionsFeed.search = e.currentTarget.value)}
            placeholder="Search collections..."
            size="md"
          >
            {#snippet leftIcon()}
              <Icon icon="lucide:search" class="h-4 w-4" />
            {/snippet}
          </EnhancedInput>
        </div>
      {/snippet}
      {#snippet loading(mode)}
        <CollectionSkeleton count={12} viewMode={mode} />
      {/snippet}
      {#snippet grid()}
        <CollectionGridView items={collectionsFeed.items} />
      {/snippet}
      {#snippet table({ table: collectionsTable, feed })}
        <DataTable table={collectionsTable!} isLoading={feed.isLoading} />
      {/snippet}
      {#snippet empty()}
        {#if collectionsFeed.search}
          <EmptyState
            kind="no-results"
            icon="ph:magnifying-glass"
            title="No collections found"
            description="Try a different search term."
          />
        {:else}
          <EmptyState
            kind="first-use"
            title="No collections yet"
            description="Group related images and share them as one link."
          >
            {#snippet preview()}
              <GhostFolders />
            {/snippet}
            {#snippet action()}
              <Button
                variant="primary"
                size="md"
                rounded="lg"
                onclick={handleCreateCollection}
              >
                <Icon icon="lucide:plus" class="h-4 w-4" />
                New collection
              </Button>
            {/snippet}
          </EmptyState>
        {/if}
      {/snippet}
      {#snippet more()}
        <LoadMoreButton
          class="mt-8"
          visible={collectionsFeed.hasMore}
          loading={collectionsFeed.isLoading}
          onclick={() => collectionsFeed.nextPage({ debounce: 300 })}
          variant="modern"
          rounded="full"
        />
      {/snippet}
    </ViewModeLayout>
  </div>
</main>

<CreateCollectionDialog modalState={createModalState} />
