<script lang="ts">
  import { useDataTable } from '@slink/ui/components/data-table';
  import type { Snippet } from 'svelte';
  import { untrack } from 'svelte';

  import type { ViewMode } from '@slink/lib/settings';
  import { useTableSettings } from '@slink/lib/settings/composables/useTableSettings.svelte';
  import type { AbstractPaginatedFeed } from '@slink/lib/state/core/AbstractPaginatedFeed.svelte';

  import {
    type ListingContext,
    type ModeConfig,
    type TableModeConfig,
    type ToolbarContext,
    isTableMode,
  } from './types';
  import {
    type ViewModeLayoutVariants,
    viewModeLayoutVariants,
  } from './view-mode-layout.variants';

  interface Props {
    feed: AbstractPaginatedFeed<any>;
    mode: ViewMode;
    config?: Partial<Record<ViewMode, ModeConfig>>;
    spacing?: ViewModeLayoutVariants['spacing'];
    onBeforeLoad?: () => boolean | void;
    onPageSizeChange?: (size: number) => void | Promise<void>;

    grid?: Snippet<[ListingContext]>;
    list?: Snippet<[ListingContext]>;
    table?: Snippet<[ListingContext]>;
    loading?: Snippet<[ViewMode]>;
    toolbar?: Snippet<[ToolbarContext]>;
    empty?: Snippet;
    more?: Snippet;
  }

  let {
    feed,
    mode,
    config,
    spacing,
    onBeforeLoad,
    onPageSizeChange,
    grid,
    list,
    table,
    loading,
    toolbar,
    empty,
    more,
  }: Props = $props();

  const tableSettings = useTableSettings(feed.key!);

  function resolveTableConfig(modeKey: ViewMode): TableModeConfig | undefined {
    const c = config?.[modeKey];
    return c && isTableMode(c) ? c : undefined;
  }

  const tableInstances = new Map<ViewMode, ReturnType<typeof useDataTable>>();

  if (config) {
    for (const [key, modeConfig] of Object.entries(config)) {
      if (modeConfig && isTableMode(modeConfig)) {
        const modeKey = key as ViewMode;
        tableInstances.set(
          modeKey,
          useDataTable({
            data: () => resolveTableConfig(modeKey)?.data ?? feed.items,
            columns: modeConfig.columns,
            currentPage: () =>
              resolveTableConfig(modeKey)?.currentPage ??
              feed.pagination.currentPage,
            totalPages: () =>
              resolveTableConfig(modeKey)?.totalPages ??
              feed.pagination.totalPages,
            onPageChange: modeConfig.onPageChange,
            tableSettings,
          }),
        );
      }
    }
  }

  const resolvedConfig = $derived.by(() => {
    const modeConfig = config?.[mode];

    if (modeConfig && isTableMode(modeConfig)) {
      return {
        toolbar: modeConfig.toolbar ?? true,
        more: modeConfig.more ?? false,
        appendMode: modeConfig.appendMode ?? ('never' as const),
      };
    }

    return {
      toolbar: modeConfig?.toolbar ?? false,
      more: modeConfig?.more ?? true,
      appendMode: modeConfig?.appendMode ?? ('always' as const),
    };
  });

  $effect(() => {
    if (feed.isCursorBased) {
      feed.setMode(resolvedConfig.appendMode);
    }
    untrack(() => feed.setPageSize(tableSettings.pageSize));

    const skipLoad = onBeforeLoad?.();
    if (!skipLoad && untrack(() => feed.needsLoad)) {
      feed.load();
    }
  });

  const handlePageSizeChange = async (size: number) => {
    if (size === tableSettings.pageSize) return;
    tableSettings.pageSize = size;

    if (onPageSizeChange) {
      await onPageSizeChange(size);
      return;
    }

    feed.setPageSize(size);
    await feed.reload();
  };

  const activeTableResult = $derived(tableInstances.get(mode));

  const context = $derived<ListingContext>({
    feed,
    handlePageSizeChange,
    tableSettings,
    table: activeTableResult?.table,
    pageSize: activeTableResult?.pageSize,
  });

  const processing = $derived(feed.showSkeleton);
  const isEmpty = $derived(feed.isEmpty);

  const modes = $derived<Partial<Record<ViewMode, Snippet<[ListingContext]>>>>({
    grid,
    list,
    table,
  });
  const active = $derived(modes[mode]);

  const toolbarContext = $derived<ToolbarContext>({
    feed,
    handlePageSizeChange,
    tableSettings,
    table: activeTableResult?.table,
    pageSize: activeTableResult?.pageSize,
    pagination: feed.pagination,
  });

  const showToolbar = $derived(toolbar && resolvedConfig.toolbar);
  const showMore = $derived(more && resolvedConfig.more);
</script>

<div class={viewModeLayoutVariants({ spacing })}>
  {#if showToolbar && toolbar}
    {@render toolbar(toolbarContext)}
  {/if}

  {#if processing && loading}
    {@render loading(mode)}
  {:else if isEmpty && empty}
    {@render empty()}
  {:else if active}
    {@render active(context)}
    {#if showMore && more}{@render more()}{/if}
  {/if}
</div>
