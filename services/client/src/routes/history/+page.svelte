<script lang="ts">
  import { LoadMoreButton } from '@slink/feature/Action';
  import {
    CollectionPickerList,
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
  import { createHistoryColumns } from '@slink/feature/Image/History/HistoryDataTable/columns';
  import {
    EmptyState,
    HistorySkeleton,
    ViewModeToggle,
  } from '@slink/feature/Layout';
  import {
    ActiveFilterBar,
    CreateTagDialog,
    TagFilter,
    TagPickerList,
  } from '@slink/feature/Tag';
  import { Subtitle, Title } from '@slink/feature/Text';
  import { Button } from '@slink/ui/components/button';
  import { DataTable, DataTableToolbar } from '@slink/ui/components/data-table';
  import { ViewModeLayout } from '@slink/ui/components/view-mode-layout';
  import { untrack } from 'svelte';

  import { page } from '$app/state';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import type { Tag as TagType } from '@slink/api/Resources/TagResource';
  import type { CollectionReference } from '@slink/api/Response/Collection/CollectionResponse';

  import { skeleton } from '@slink/lib/actions/skeleton';
  import { createTagFilterManager } from '@slink/lib/composables/useTagFilterUrl';
  import { createImageSelectionState } from '@slink/lib/state/ImageSelectionState.svelte';
  import { useUploadHistoryFeed } from '@slink/lib/state/UploadHistoryFeed.svelte';

  import { cn } from '@slink/utils/ui';

  const { settings } = page.data;

  const historyFeedState = useUploadHistoryFeed();
  const tagFilterManager = $derived(createTagFilterManager(page.url));
  const selectionState = createImageSelectionState();

  $effect(() => {
    const search = page.url.search;

    untrack(() => {
      const currentIds = historyFeedState.tagFilter.selectedTags
        .map((t: TagType) => t.id)
        .sort()
        .join(',');
      const urlTagParam = new URLSearchParams(search).get('tagIds');
      const urlIds = urlTagParam
        ? urlTagParam.split(',').filter(Boolean).sort().join(',')
        : '';

      if (currentIds === urlIds) return;

      loadTagFiltersFromUrl();
    });
  });

  const batchActions = createBatchActionsState(
    selectionState,
    () => historyFeedState.items,
  );

  const batchCollectionPicker = createBatchCollectionPickerState(batchActions);
  const batchTagPicker = createBatchTagPickerState(batchActions);

  const historyColumns = createHistoryColumns(
    () => selectionState,
    () => historyFeedState.items.map((item) => item.id),
    {
      onDelete: onImageDelete,
      onCollectionChange: onCollectionChange,
      onTagChange: onTagChange,
    },
  );

  function onImageDelete(id: string) {
    historyFeedState.removeItem(id);
  }

  function onCollectionChange(
    imageId: string,
    collections: CollectionReference[],
  ) {
    historyFeedState.update(imageId, { collections });
  }

  function onTagChange(imageId: string, tags: TagType[]) {
    historyFeedState.update(imageId, { tags });
  }

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
          <Title size="md">Upload History</Title>
          <Subtitle>View and manage your uploaded images</Subtitle>
        </div>

        <div class="flex items-center gap-3 shrink-0">
          <ViewModeToggle
            value={settings.history.viewMode}
            modes={['grid', 'list', 'table']}
            on={{
              change: (mode) => {
                settings.history = { viewMode: mode };
              },
            }}
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

    {#key filterKey}
      <ViewModeLayout
        feed={historyFeedState}
        mode={settings.history.viewMode}
        onBeforeLoad={() => {
          if (tagFilterManager.hasFiltersInUrl()) {
            loadTagFiltersFromUrl();
            return true;
          }
        }}
        config={{
          table: {
            columns: historyColumns,
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
          <HistorySkeleton count={12} viewMode={mode} />
        {/snippet}
        {#snippet grid(_ctx)}
          <HistoryGridView
            items={historyFeedState.items}
            {selectionState}
            on={{
              delete: onImageDelete,
              collectionChange: onCollectionChange,
              tagChange: onTagChange,
            }}
          />
        {/snippet}
        {#snippet list(_ctx)}
          <HistoryListView
            items={historyFeedState.items}
            {selectionState}
            on={{
              delete: onImageDelete,
              collectionChange: onCollectionChange,
              tagChange: onTagChange,
            }}
          />
        {/snippet}
        {#snippet table({ table: historyTable, feed })}
          <DataTable
            table={historyTable!}
            isLoading={feed.isLoading}
            onRowClick={(item) => selectionState.select(item.id)}
          />
        {/snippet}
        {#snippet empty()}
          {#if historyFeedState.hasActiveFilter}
            <EmptyState
              icon="heroicons:funnel"
              title="No matching images"
              description="No images match your current tag filters. Try removing some tags or clearing all filters."
              variant="blue"
              size="md"
            />
          {:else}
            <EmptyState
              icon="ph:clock-clockwise-duotone"
              title="No history yet"
              description="Your upload history will appear here. Start uploading images to see your files and manage them easily."
              variant="purple"
              size="md"
            >
              {#snippet action()}
                <Button
                  variant="soft-violet"
                  size="md"
                  rounded="full"
                  href="/upload"
                >
                  <Icon icon="ph:upload-simple" class="h-4 w-4" />
                  Upload Images
                </Button>
              {/snippet}
            </EmptyState>
          {/if}
        {/snippet}
        {#snippet more()}
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
        {/snippet}
      </ViewModeLayout>
    {/key}
  </div>
</section>

{#if selectionState.hasSelection}
  <SelectionActionBar
    selectedCount={selectionState.selectedCount}
    totalCount={historyFeedState.items.length}
    onSelectAll={handleSelectAll}
    onDeselectAll={handleDeselectAll}
    onCancel={() => selectionState.exitSelectionMode()}
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
        <CollectionPickerList
          collections={batchCollectionPicker.picker.items}
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
        <TagPickerList
          tags={batchTagPicker.picker.items}
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
