<script lang="ts">
  import { LoadMoreButton } from '@slink/feature/Action';
  import {
    CollectionGridView,
    CreateCollectionForm,
  } from '@slink/feature/Collection';
  import { collectionColumns } from '@slink/feature/Collection/CollectionViews/CollectionDataTable/columns';
  import {
    CollectionSkeleton,
    EmptyState,
    ViewModeToggle,
  } from '@slink/feature/Layout';
  import { Subtitle, Title } from '@slink/feature/Text';
  import { DataTable, DataTableToolbar } from '@slink/ui/components/data-table';
  import { Dialog } from '@slink/ui/components/dialog';
  import { SplitButton } from '@slink/ui/components/split-button';
  import { ViewModeLayout } from '@slink/ui/components/view-mode-layout';

  import { page } from '$app/state';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { ValidationException } from '@slink/api/Exceptions';

  import { skeleton } from '@slink/lib/actions/skeleton';
  import { useCollectionListFeed } from '@slink/lib/state/CollectionListFeed.svelte';

  const { settings } = page.data;

  const collectionsFeed = useCollectionListFeed();
  collectionsFeed.reset();

  let createModalOpen = $state(false);
  let createFormErrors = $state<Record<string, string>>({});
  let isCreating = $state(false);

  async function handleCreateSubmit(data: {
    name: string;
    description?: string;
  }) {
    try {
      isCreating = true;
      createFormErrors = {};

      await collectionsFeed.createCollection(data.name, data.description);
      createModalOpen = false;
    } catch (error) {
      if (error instanceof ValidationException && error.violations) {
        createFormErrors = error.violations.reduce<Record<string, string>>(
          (acc, violation) => {
            acc[violation.property] = violation.message;
            return acc;
          },
          {},
        );
      }
    } finally {
      isCreating = false;
    }
  }

  function handleCloseCreateModal() {
    createModalOpen = false;
    createFormErrors = {};
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
          <SplitButton onclick={() => (createModalOpen = true)}>
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
        />
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
          title="No collections yet"
          description="Create your first collection to organize and share your images."
          actionText="Create Collection"
          variant="purple"
          size="md"
          actionClick={() => (createModalOpen = true)}
        />
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

<Dialog bind:open={createModalOpen} size="md">
  {#snippet children()}
    <CreateCollectionForm
      isSubmitting={isCreating}
      errors={createFormErrors}
      onSubmit={handleCreateSubmit}
      onCancel={handleCloseCreateModal}
    />
  {/snippet}
</Dialog>
