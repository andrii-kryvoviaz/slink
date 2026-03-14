<script lang="ts">
  import { LoadMoreButton } from '@slink/feature/Action';
  import {
    CollectionDataTable,
    CollectionGridView,
    CreateCollectionForm,
  } from '@slink/feature/Collection';
  import {
    CollectionSkeleton,
    EmptyState,
    ViewModeToggle,
  } from '@slink/feature/Layout';
  import { Subtitle, Title } from '@slink/feature/Text';
  import { Dialog } from '@slink/ui/components/dialog';
  import { SplitButton } from '@slink/ui/components/split-button';
  import { untrack } from 'svelte';

  import { page } from '$app/state';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { ValidationException } from '@slink/api/Exceptions';

  import { skeleton } from '@slink/lib/actions/skeleton';
  import { type ViewMode } from '@slink/lib/settings';
  import { useTableSettings } from '@slink/lib/settings/composables/useTableSettings.svelte';
  import { useCollectionListFeed } from '@slink/lib/state/CollectionListFeed.svelte';

  const { settings } = page.data;

  const collectionsFeed = useCollectionListFeed();
  collectionsFeed.reset();

  const tableSettings = useTableSettings('collections');

  let createModalOpen = $state(false);
  let createFormErrors = $state<Record<string, string>>({});
  let isCreating = $state(false);

  let viewMode = $derived(settings.collections.viewMode);
  let showSkeleton = $derived(
    collectionsFeed.showSkeleton || !collectionsFeed.isDirty,
  );

  $effect(() => {
    collectionsFeed.setMode(viewMode === 'table' ? 'never' : 'always');
    untrack(() => collectionsFeed.setPageSize(tableSettings.pageSize));

    if (untrack(() => collectionsFeed.needsLoad)) {
      collectionsFeed.load();
    }
  });

  const handleViewModeChange = (newViewMode: ViewMode) => {
    settings.collections = { viewMode: newViewMode };
  };

  const handleTablePageSizeChange = async (size: number) => {
    tableSettings.pageSize = size;
    collectionsFeed.setPageSize(size);
    await collectionsFeed.reload();
  };

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
            value={viewMode}
            modes={['grid', 'table']}
            on={{ change: handleViewModeChange }}
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

    {#if viewMode === 'grid'}
      {#if showSkeleton}
        <div in:fade={{ duration: 200 }}>
          <CollectionSkeleton count={12} viewMode="grid" />
        </div>
      {:else if collectionsFeed.isEmpty}
        <div in:fade={{ duration: 200 }}>
          <EmptyState
            icon="ph:folder-simple-duotone"
            title="No collections yet"
            description="Create your first collection to organize and share your images."
            actionText="Create Collection"
            variant="purple"
            size="md"
            actionClick={() => (createModalOpen = true)}
          />
        </div>
      {:else}
        <div in:fade={{ duration: 400 }}>
          <CollectionGridView items={collectionsFeed.items} />

          <LoadMoreButton
            class="mt-8"
            visible={collectionsFeed.hasMore}
            loading={collectionsFeed.isLoading}
            onclick={() => collectionsFeed.nextPage({ debounce: 300 })}
            variant="modern"
            rounded="full"
          />
        </div>
      {/if}
    {:else}
      <div in:fade={{ duration: 200 }}>
        <CollectionDataTable
          items={collectionsFeed.items}
          {tableSettings}
          isLoading={collectionsFeed.isLoading}
          {showSkeleton}
          pagination={collectionsFeed.pagination}
          on={{
            nextPage: () => collectionsFeed.nextPage(),
            prevPage: () => collectionsFeed.prevPage(),
            pageSizeChange: handleTablePageSizeChange,
          }}
        />
      </div>
    {/if}
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
