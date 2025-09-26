<script lang="ts">
  import { LoadMoreButton } from '@slink/feature/Action';
  import { HistoryGridView, HistoryListView } from '@slink/feature/Image';
  import { EmptyState } from '@slink/feature/Layout';
  import { HistorySkeleton } from '@slink/feature/Layout';
  import { TagFilter } from '@slink/feature/Tag';
  import { ToggleGroup } from '@slink/ui/components';
  import type { ToggleGroupOption } from '@slink/ui/components';

  import { page } from '$app/stores';
  import { fade } from 'svelte/transition';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { skeleton } from '@slink/lib/actions/skeleton';
  import { createTagFilterManager } from '@slink/lib/composables/useTagFilterUrl';
  import { settings } from '@slink/lib/settings';
  import type { HistoryViewMode } from '@slink/lib/settings/setters/history';
  import { useUploadHistoryFeed } from '@slink/lib/state/UploadHistoryFeed.svelte';

  const historyFeedState = useUploadHistoryFeed();
  const tagFilterManager = createTagFilterManager($page.url);

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

  const filterKey = $derived(
    `${historyFeedState.tagFilter.selectedTags.map((t) => t.id).join(',')}-${historyFeedState.tagFilter.requireAllTags}`,
  );
</script>

<svelte:head>
  <title>Upload History | Slink</title>
</svelte:head>

<section in:fade={{ duration: 300 }}>
  <div
    class="flex flex-col px-4 py-6 sm:px-6 w-full"
    use:skeleton={{
      feed: historyFeedState,
      minDisplayTime: 300,
      showDelay: 200,
    }}
  >
    <div class="mb-8 space-y-6" in:fade={{ duration: 400, delay: 100 }}>
      <div class="flex items-center justify-between w-full">
        <div class="flex-1 min-w-0">
          <h1 class="text-3xl font-semibold text-slate-900 dark:text-white">
            Upload History
          </h1>
          <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            View and manage your uploaded images
          </p>
        </div>

        <ToggleGroup
          value={viewMode}
          options={viewModeOptions}
          onValueChange={handleViewModeChange}
          aria-label="View mode selection"
          className="ml-4"
        />
      </div>

      <TagFilter
        selectedTags={historyFeedState.tagFilter.selectedTags}
        requireAllTags={historyFeedState.tagFilter.requireAllTags}
        onFilterChange={handleTagFilterChange}
        onClearFilter={handleClearTagFilter}
        disabled={historyFeedState.isLoading}
      />
    </div>

    {#if historyFeedState.showSkeleton}
      <div in:fade={{ duration: 200 }}>
        <HistorySkeleton count={6} />
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
      <div in:fade={{ duration: 400, delay: 200 }}>
        {#key filterKey}
          {#if viewMode === 'grid'}
            <HistoryGridView
              items={historyFeedState.items}
              on={{ delete: onImageDelete }}
            />
          {:else}
            <HistoryListView
              items={historyFeedState.items}
              on={{ delete: onImageDelete }}
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
