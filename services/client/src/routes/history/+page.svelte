<script lang="ts">
  import { ApiClient } from '@slink/api';
  import { LoadMoreButton } from '@slink/feature/Action';
  import {
    HistoryGridView,
    HistoryListView,
    SelectionActionBar,
  } from '@slink/feature/Image';
  import {
    EmptyState,
    HistorySkeleton,
    ViewModeToggle,
  } from '@slink/feature/Layout';
  import { ActiveFilterBar, TagFilter } from '@slink/feature/Tag';
  import { untrack } from 'svelte';

  import { page } from '$app/state';
  import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { Tag } from '@slink/api/Resources/TagResource';

  import { skeleton } from '@slink/lib/actions/skeleton';
  import { createTagFilterManager } from '@slink/lib/composables/useTagFilterUrl';
  import { settings } from '@slink/lib/settings';
  import type { ViewMode } from '@slink/lib/settings/setters/viewMode';
  import { createImageSelectionState } from '@slink/lib/state/ImageSelectionState.svelte';
  import { useUploadHistoryFeed } from '@slink/lib/state/UploadHistoryFeed.svelte';

  const historyFeedState = useUploadHistoryFeed();
  const tagFilterManager = createTagFilterManager(page.url);
  const selectionState = createImageSelectionState();

  let viewMode = $derived<ViewMode>(
    page.data.settings?.history?.viewMode || 'list',
  );

  const {
    isLoading: batchDeleteIsLoading,
    error: batchDeleteError,
    data: batchDeleteData,
    run: batchDelete,
  } = ReactiveState<{
    deleted: string[];
    failed: Array<{ id: string; reason: string }>;
  }>(
    (imageIds: string[], preserveOnDisk: boolean) =>
      ApiClient.image.batchRemove(imageIds, preserveOnDisk),
    { minExecutionTime: 300 },
  );

  $effect(() => {
    settings.set('history', { viewMode });
  });

  $effect(() => {
    if (tagFilterManager.hasFiltersInUrl()) {
      loadTagFiltersFromUrl();
    } else if (untrack(() => historyFeedState.needsLoad)) {
      historyFeedState.load();
    }
  });

  const loadTagFiltersFromUrl = async () => {
    try {
      const { tags, requireAllTags } = await tagFilterManager.loadFromUrl();

      if (tags.length > 0) {
        historyFeedState.setTagFilter(tags, requireAllTags);
        await historyFeedState.load();
      } else if (historyFeedState.needsLoad) {
        historyFeedState.load();
      }
    } catch (error) {
      console.error('Failed to load tag filters from URL:', error);
      if (historyFeedState.needsLoad) {
        historyFeedState.load();
      }
    }
  };

  const handleViewModeChange = (newViewMode: ViewMode) => {
    viewMode = newViewMode;
  };

  const onImageDelete = (id: string) => {
    historyFeedState.removeItem(id);
  };

  const onCollectionChange = (imageId: string, collectionIds: string[]) => {
    historyFeedState.update(imageId, { collectionIds });
  };

  const handleTagFilterChange = async (
    tags: Tag[],
    requireAllTags: boolean,
  ) => {
    historyFeedState.setTagFilter(tags, requireAllTags);
    await tagFilterManager.updateUrl(tags, requireAllTags);
    await historyFeedState.load();
  };

  const handleClearTagFilter = async () => {
    historyFeedState.clearTagFilter();
    await tagFilterManager.clearUrl();
    await historyFeedState.load();
  };

  const handleMatchModeChange = async (requireAllTags: boolean) => {
    const tags = historyFeedState.tagFilter.selectedTags;
    historyFeedState.setTagFilter(tags, requireAllTags);
    await tagFilterManager.updateUrl(tags, requireAllTags);
    await historyFeedState.load();
  };

  const handleEnterSelectionMode = () => {
    selectionState.enterSelectionMode();
  };

  const handleExitSelectionMode = () => {
    selectionState.exitSelectionMode();
  };

  const handleSelectAll = () => {
    const allIds = historyFeedState.items.map((item) => item.id);
    selectionState.selectAll(allIds);
  };

  const handleDeselectAll = () => {
    selectionState.deselectAll();
  };

  const handleBulkDelete = async (preserveOnDisk: boolean) => {
    const idsToDelete = selectionState.selectedIds;
    await batchDelete(idsToDelete, preserveOnDisk);

    if ($batchDeleteError) {
      toast.error('Failed to delete images. Please try again later.');
      return;
    }

    const result = $batchDeleteData;
    if (!result) {
      toast.error('Failed to delete images. Please try again later.');
      return;
    }

    if (result.deleted.length > 0) {
      result.deleted.forEach((id) => historyFeedState.removeItem(id));
      selectionState.removeIds(result.deleted);
      toast.success(
        `Successfully deleted ${result.deleted.length} ${result.deleted.length > 1 ? 'images' : 'image'} from history`,
      );
    }

    if (result.failed.length > 0) {
      toast.error(
        `Failed to delete ${result.failed.length} ${result.failed.length > 1 ? 'images' : 'image'}`,
      );
    }

    selectionState.exitSelectionMode();
  };

  const filterKey = $derived(
    `${historyFeedState.tagFilter.selectedTags.map((t) => t.id).join(',')}-${historyFeedState.tagFilter.requireAllTags}`,
  );
</script>

<svelte:head>
  <title>Upload History | Slink</title>
</svelte:head>

<section>
  <div
    class="flex flex-col px-4 py-6 sm:px-6 w-full"
    use:skeleton={{ feed: historyFeedState }}
  >
    <div class="mb-8 space-y-6" in:fade={{ duration: 300 }}>
      <div
        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between w-full"
      >
        <div class="min-w-0">
          <h1
            class="text-2xl sm:text-3xl font-semibold text-slate-900 dark:text-white"
          >
            Upload History
          </h1>
          <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            View and manage your uploaded images
          </p>
        </div>

        <div class="flex items-center gap-3 shrink-0">
          {#if !historyFeedState.isEmpty}
            {#if selectionState.isSelectionMode}
              <button
                type="button"
                onclick={handleExitSelectionMode}
                class="flex items-center justify-center gap-1.5 px-3 py-1.5 text-sm font-medium text-slate-400 dark:text-slate-500 hover:text-red-500 dark:hover:text-red-400 transition-colors duration-200"
              >
                <Icon icon="lucide:x" class="w-4 h-4" />
                <span>Cancel</span>
              </button>
            {:else}
              <button
                type="button"
                onclick={handleEnterSelectionMode}
                class="flex items-center justify-center gap-1.5 px-3 py-1.5 text-sm font-medium text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 transition-colors duration-200"
              >
                <Icon
                  icon="lucide:square-dashed-mouse-pointer"
                  class="w-4 h-4"
                />
                <span>Select</span>
              </button>
            {/if}
          {/if}

          <ViewModeToggle
            value={viewMode}
            modes={['grid', 'list']}
            on={{ change: handleViewModeChange }}
          />
        </div>
      </div>

      <div>
        <TagFilter
          selectedTags={historyFeedState.tagFilter.selectedTags}
          requireAllTags={historyFeedState.tagFilter.requireAllTags}
          onFilterChange={handleTagFilterChange}
          disabled={historyFeedState.isLoading}
          variant="neon"
        />

        {#if historyFeedState.hasActiveFilter}
          <ActiveFilterBar
            selectedTags={historyFeedState.tagFilter.selectedTags}
            requireAllTags={historyFeedState.tagFilter.requireAllTags}
            onClear={handleClearTagFilter}
            onMatchModeChange={handleMatchModeChange}
            disabled={historyFeedState.isLoading}
          />
        {/if}
      </div>
    </div>

    {#if historyFeedState.showSkeleton}
      <div in:fade={{ duration: 200 }}>
        <HistorySkeleton count={12} {viewMode} />
      </div>
    {:else if historyFeedState.isEmpty && historyFeedState.hasActiveFilter}
      <div in:fade={{ duration: 200 }}>
        <EmptyState
          icon="heroicons:funnel"
          title="No matching images"
          description="No images match your current tag filters. Try removing some tags or clearing all filters."
          variant="blue"
          size="md"
        />
      </div>
    {:else if historyFeedState.isEmpty}
      <div in:fade={{ duration: 200 }}>
        <EmptyState
          icon="ph:clock-clockwise-duotone"
          title="No history yet"
          description="Your upload history will appear here. Start uploading images to see your files and manage them easily."
          actionText="Upload Images"
          actionHref="/upload"
          variant="purple"
          size="md"
        />
      </div>
    {:else}
      <div in:fade={{ duration: 400 }}>
        {#key filterKey}
          {#if viewMode === 'grid'}
            <HistoryGridView
              items={historyFeedState.items}
              {selectionState}
              on={{
                delete: onImageDelete,
                collectionChange: onCollectionChange,
              }}
            />
          {:else}
            <HistoryListView
              items={historyFeedState.items}
              {selectionState}
              on={{
                delete: onImageDelete,
                collectionChange: onCollectionChange,
              }}
            />
          {/if}
        {/key}
      </div>
    {/if}

    <LoadMoreButton
      class="mt-8"
      visible={historyFeedState.hasMore}
      loading={historyFeedState.isLoading}
      onclick={() =>
        historyFeedState.nextPage({
          debounce: 300,
        })}
      variant="modern"
      rounded="full"
    >
      {#snippet text()}
        <span>View More</span>
      {/snippet}
    </LoadMoreButton>
  </div>
</section>

{#if selectionState.hasSelection}
  <SelectionActionBar
    selectedCount={selectionState.selectedCount}
    totalCount={historyFeedState.items.length}
    loading={batchDeleteIsLoading}
    onSelectAll={handleSelectAll}
    onDeselectAll={handleDeselectAll}
    onDelete={handleBulkDelete}
    onCancel={handleExitSelectionMode}
  />
{/if}
