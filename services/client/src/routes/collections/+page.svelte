<script lang="ts">
  import { LoadMoreButton } from '@slink/feature/Action';
  import {
    CollectionGridView,
    CreateCollectionDialog,
  } from '@slink/feature/Collection';
  import { collectionColumns } from '@slink/feature/Collection/CollectionViews/CollectionDataTable/columns';
  import {
    CollectionSkeleton,
    EmptyState,
    ViewModeToggle,
  } from '@slink/feature/Layout';
  import { Subtitle, Title } from '@slink/feature/Text';
  import { Button } from '@slink/ui/components/button';
  import { DataTable, DataTableToolbar } from '@slink/ui/components/data-table';
  import { EnhancedInput } from '@slink/ui/components/input';
  import { SplitButton } from '@slink/ui/components/split-button';
  import { ViewModeLayout } from '@slink/ui/components/view-mode-layout';

  import { page } from '$app/state';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { skeleton } from '@slink/lib/actions/skeleton';
  import { useCollectionListFeed } from '@slink/lib/state/CollectionListFeed.svelte';
  import { createCreateCollectionModalState } from '@slink/lib/state/CreateCollectionModalState.svelte';

  const { settings } = page.data;

  const collectionsFeed = useCollectionListFeed();
  collectionsFeed.reset();

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
    <div class="mb-8 space-y-6" in:fade={{ duration: 400, delay: 100 }}>
      <div class="flex items-center justify-between w-full">
        <div class="flex-1 min-w-0">
          <Title>Collections</Title>
          <Subtitle>Organize your images into albums</Subtitle>
        </div>

        <div class="flex items-center gap-3 shrink-0">
          <ViewModeToggle
            value={settings.collections.viewMode}
            modes={['grid', 'table']}
            on={{
              change: (mode) => {
                settings.collections = { viewMode: mode };
              },
            }}
          />
          <SplitButton onclick={handleCreateCollection}>
            Create
            {#snippet aside()}
              <Icon icon="lucide:plus" class="w-3.5 h-3.5" />
            {/snippet}
          </SplitButton>
        </div>
      </div>
    </div>

    <ViewModeLayout
      feed={collectionsFeed}
      mode={settings.collections.viewMode}
      config={{
        grid: {
          toolbar: true,
        },
        table: {
          columns: collectionColumns,
        },
      }}
    >
      {#snippet toolbar({
        table,
        pageSize,
        pagination,
        feed,
        handlePageSizeChange,
      })}
        <DataTableToolbar
          {table}
          {pageSize}
          {pagination}
          onPageSizeChange={handlePageSizeChange}
          onNextPage={() => feed.nextPage()}
          onPrevPage={() => feed.prevPage()}
          isLoading={feed.isLoading}
          showPagination={!!table}
          showColumnToggle={!!table}
        >
          {#snippet leading()}
            <div class="lg:max-w-sm">
              <EnhancedInput
                debounce={300}
                oninput={(e) =>
                  (collectionsFeed.search = e.currentTarget.value)}
                placeholder="Search collections..."
                size="md"
              >
                {#snippet leftIcon()}
                  <Icon icon="lucide:search" class="h-4 w-4" />
                {/snippet}
              </EnhancedInput>
            </div>
          {/snippet}
        </DataTableToolbar>
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
        <EmptyState
          icon="ph:folder-simple-duotone"
          title="No collections found"
          description={collectionsFeed.search
            ? 'Try adjusting your search term'
            : 'Create your first collection to organize and share your images.'}
          variant="purple"
          size="md"
        >
          {#snippet action()}
            {#if !collectionsFeed.search}
              <Button
                variant="soft-violet"
                size="lg"
                rounded="full"
                onclick={handleCreateCollection}
              >
                <Icon icon="lucide:plus" class="h-4 w-4" />
                Create Collection
              </Button>
            {/if}
          {/snippet}
        </EmptyState>
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
