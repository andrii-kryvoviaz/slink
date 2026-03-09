<script lang="ts">
  import { LoadMoreButton } from '@slink/feature/Action';
  import {
    CollectionListView,
    CreateCollectionDialog,
  } from '@slink/feature/Collection';
  import {
    BatchPickerAction,
    DeleteAction,
    DownloadAction,
    HistoryGridView,
    HistoryListView,
    SelectionActionBar,
    VisibilityAction,
    createBatchActionsState,
    createBatchCollectionPickerState,
    createBatchTagPickerState,
  } from '@slink/feature/Image';
  import {
    EmptyState,
    HistorySkeleton,
    ViewModeToggle,
  } from '@slink/feature/Layout';
  import {
    ActiveFilterBar,
    CreateTagDialog,
    TagFilter,
    TagListView,
  } from '@slink/feature/Tag';
  import { Button } from '@slink/ui/components/button';
  import { untrack } from 'svelte';

  import { page } from '$app/state';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import type { Tag as TagType } from '@slink/api/Resources/TagResource';

  import { skeleton } from '@slink/lib/actions/skeleton';
  import { createTagFilterManager } from '@slink/lib/composables/useTagFilterUrl';
  import { type ViewMode } from '@slink/lib/settings';
  import { createImageSelectionState } from '@slink/lib/state/ImageSelectionState.svelte';
  import { useUploadHistoryFeed } from '@slink/lib/state/UploadHistoryFeed.svelte';

  import { cn } from '@slink/utils/ui';

  const { settings } = page.data;

  const historyFeedState = useUploadHistoryFeed();
  const tagFilterManager = createTagFilterManager(page.url);
  const selectionState = createImageSelectionState();

  const batchActions = createBatchActionsState(
    selectionState,
    () => historyFeedState.items,
  );

  const batchCollectionPicker = createBatchCollectionPickerState(batchActions);
  const batchTagPicker = createBatchTagPickerState(batchActions);

  let viewMode = $derived(settings.history.viewMode);

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
    settings.history = { viewMode: newViewMode };
  };

  const onImageDelete = (id: string) => {
    historyFeedState.removeItem(id);
  };

  const onCollectionChange = (imageId: string, collectionIds: string[]) => {
    historyFeedState.update(imageId, { collectionIds });
  };

  const handleTagFilterChange = async (
    tags: TagType[],
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

  const filterKey = $derived(
    `${historyFeedState.tagFilter.selectedTags.map((t) => t.id).join(',')}-${historyFeedState.tagFilter.requireAllTags}`,
  );
</script>

<svelte:head>
  <title>Upload History | Slink</title>
</svelte:head>

<section>
  <div
    class={cn(
      'flex flex-col px-4 py-6 sm:px-6 w-full',
      selectionState.hasSelection && 'pb-24',
    )}
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
              <Button
                variant="toggle"
                size="xs"
                rounded="lg"
                class="w-22"
                onclick={handleExitSelectionMode}
              >
                <Icon icon="lucide:x" class="w-3.5 h-3.5" />
                <span>Cancel</span>
              </Button>
            {:else}
              <Button
                variant="toggle"
                size="xs"
                rounded="lg"
                class="w-22"
                onclick={handleEnterSelectionMode}
              >
                <Icon
                  icon="lucide:square-dashed-mouse-pointer"
                  class="w-3.5 h-3.5"
                />
                <span>Select</span>
              </Button>
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
    onSelectAll={handleSelectAll}
    onDeselectAll={handleDeselectAll}
    onCancel={handleExitSelectionMode}
  >
    {#snippet actions()}
      <VisibilityAction
        selectedCount={selectionState.selectedCount}
        loading={batchActions.isLoading}
        onAction={batchActions.updateVisibility}
      />
      <BatchPickerAction
        icon="lucide:folder"
        label="Collection"
        confirmLabel="Add to Collection"
        selectedCount={selectionState.selectedCount}
        pendingCount={batchCollectionPicker.selection.changeCount}
        loading={batchActions.isLoading}
        bind:open={batchCollectionPicker.open}
        onOpen={batchCollectionPicker.handleOpen}
        onApply={batchCollectionPicker.apply}
      >
        <CollectionListView
          collections={batchCollectionPicker.picker.collections}
          isLoading={batchCollectionPicker.picker.isLoading}
          variant="popover"
          showSearch
          getItemState={(id) => batchCollectionPicker.selection.getState(id)}
          onToggle={(collection) =>
            batchCollectionPicker.selection.toggle(collection.id)}
          onCreateNew={batchCollectionPicker.handleCreateNew}
        />
      </BatchPickerAction>
      <BatchPickerAction
        icon="lucide:tag"
        label="Tag"
        confirmLabel="Assign Tags"
        selectedCount={selectionState.selectedCount}
        pendingCount={batchTagPicker.selection.changeCount}
        loading={batchActions.isLoading}
        bind:open={batchTagPicker.open}
        onOpen={batchTagPicker.handleOpen}
        onApply={batchTagPicker.apply}
      >
        <TagListView
          tags={batchTagPicker.picker.tags}
          isLoading={batchTagPicker.picker.isLoading}
          variant="popover"
          showSearch
          getItemState={(id) => batchTagPicker.selection.getState(id)}
          onToggle={(tag) => batchTagPicker.selection.toggle(tag.id)}
          onCreateNew={batchTagPicker.handleCreateNew}
        />
      </BatchPickerAction>
      <DownloadAction
        loading={batchActions.isLoading}
        onAction={batchActions.download}
      />
      <div class="h-6 w-px bg-gray-200 dark:bg-gray-700"></div>
      <DeleteAction
        selectedCount={selectionState.selectedCount}
        loading={batchActions.isLoading}
        onAction={batchActions.delete}
      />
    {/snippet}
  </SelectionActionBar>
{/if}

<CreateCollectionDialog modalState={batchCollectionPicker.modal} />
<CreateTagDialog modalState={batchTagPicker.modal} />
