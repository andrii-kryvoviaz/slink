<script lang="ts">
  import { LoadMoreButton } from '@slink/feature/Action';
  import {
    HistoryGridView,
    HistoryListView,
    SelectionActionBar,
  } from '@slink/feature/Image';
  import { EmptyState } from '@slink/feature/Layout';
  import { HistorySkeleton } from '@slink/feature/Layout';
  import { TagFilter } from '@slink/feature/Tag';
  import { ToggleGroup } from '@slink/ui/components';
  import type { ToggleGroupOption } from '@slink/ui/components';

  import { page } from '$app/stores';
  import { toast } from '$lib/utils/ui/toast-sonner.svelte.js';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { Tag } from '@slink/api/Resources/TagResource';

  import { skeleton } from '@slink/lib/actions/skeleton';
  import { createTagFilterManager } from '@slink/lib/composables/useTagFilterUrl';
  import { settings } from '@slink/lib/settings';
  import type { HistoryViewMode } from '@slink/lib/settings/setters/history';
  import { createImageSelectionState } from '@slink/lib/state/ImageSelectionState.svelte';
  import { useUploadHistoryFeed } from '@slink/lib/state/UploadHistoryFeed.svelte';

  const historyFeedState = useUploadHistoryFeed();
  const tagFilterManager = createTagFilterManager($page.url);
  const selectionState = createImageSelectionState();

  const serverSettings = $page.data.settings;

  let viewMode = $state<HistoryViewMode>(
    serverSettings?.history?.viewMode || 'list',
  );

  const viewModeOptions: ToggleGroupOption<HistoryViewMode>[] = [
    {
      value: 'grid',
      label: 'Grid',
      icon: 'heroicons:squares-2x2',
    },
    {
      value: 'list',
      label: 'List',
      icon: 'heroicons:bars-3',
    },
  ];

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
    } else if (!historyFeedState.isDirty) {
      historyFeedState.load();
    }
  });

  const loadTagFiltersFromUrl = async () => {
    try {
      const { tags, requireAllTags } = await tagFilterManager.loadFromUrl();

      if (tags.length > 0) {
        historyFeedState.setTagFilter(tags, requireAllTags);
        await historyFeedState.load();
      } else if (!historyFeedState.isDirty) {
        historyFeedState.load();
      }
    } catch (error) {
      console.error('Failed to load tag filters from URL:', error);
      if (!historyFeedState.isDirty) {
        historyFeedState.load();
      }
    }
  };

  const handleViewModeChange = (newViewMode: HistoryViewMode) => {
    if (newViewMode !== viewMode) {
      historyFeedState.reset();
      historyFeedState.load();
    }
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
    use:skeleton={{
      feed: historyFeedState,
      minDisplayTime: 300,
      showDelay: 200,
    }}
  >
    <div class="mb-8 space-y-6" in:fade={{ duration: 300 }}>
      <div class="flex items-center justify-between w-full">
        <div class="flex-1 min-w-0">
          <h1 class="text-3xl font-semibold text-slate-900 dark:text-white">
            Upload History
          </h1>
          <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            View and manage your uploaded images
          </p>
        </div>

        <div
          class="flex items-center gap-1 ml-4 bg-gradient-to-br from-slate-50 to-slate-100/50 dark:from-slate-800/50 dark:to-slate-700/30 rounded-xl p-1 border border-slate-200 dark:border-slate-700"
        >
          {#if !historyFeedState.isEmpty}
            {#if selectionState.isSelectionMode}
              <button
                type="button"
                onclick={handleExitSelectionMode}
                class="flex items-center justify-center gap-1.5 px-3 py-1.5 text-sm font-medium text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-slate-100/50 dark:hover:bg-slate-700/30 rounded-lg transition-colors duration-200"
              >
                <Icon icon="heroicons:x-mark" class="w-4 h-4" />
                <span>Cancel</span>
              </button>
            {:else}
              <button
                type="button"
                onclick={handleEnterSelectionMode}
                class="flex items-center justify-center gap-1.5 px-3 py-1.5 text-sm font-medium text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-slate-100/50 dark:hover:bg-slate-700/30 rounded-lg transition-colors duration-200"
              >
                <Icon icon="heroicons:check-circle" class="w-4 h-4" />
                <span>Select</span>
              </button>
            {/if}
            <div class="w-px h-5 bg-slate-200 dark:bg-slate-700"></div>
          {/if}

          <ToggleGroup
            value={viewMode}
            options={viewModeOptions}
            onValueChange={handleViewModeChange}
            aria-label="View mode selection"
            className="!p-0 !border-0 !bg-transparent"
          />
        </div>
      </div>

      <TagFilter
        selectedTags={historyFeedState.tagFilter.selectedTags}
        requireAllTags={historyFeedState.tagFilter.requireAllTags}
        onFilterChange={handleTagFilterChange}
        onClearFilter={handleClearTagFilter}
        disabled={historyFeedState.isLoading}
        variant="neon"
      />
    </div>

    {#if historyFeedState.showSkeleton}
      <div in:fade={{ duration: 200 }}>
        <HistorySkeleton count={6} {viewMode} />
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
