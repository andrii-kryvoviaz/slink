<script lang="ts">
  import {
    ImageActionBar,
    ImagePlaceholder,
    ViewCountBadge,
    VisibilityBadge,
  } from '@slink/feature/Image';
  import { calculateHistoryCardWeight } from '@slink/feature/Image/utils/calculateHistoryCardWeight';
  import { Masonry } from '@slink/feature/Layout';
  import { ImageTagList } from '@slink/feature/Tag';
  import { ImageCollectionList } from '@slink/feature/Collection';
  import { OverlayCheckbox } from '@slink/ui/components/checkbox';

  import { fade, fly } from 'svelte/transition';

  import { cn } from '@slink/utils/ui';

  import {
    actionBarVisibilityVariants,
    createActionBarImage,
    historyActionBarButtons,
    historyCardVariants,
    linkVariants,
  } from './HistoryView.theme';
  import type { HistoryViewProps } from './HistoryView.types';
  import ImageMetadata from './ImageMetadata.svelte';
  import { useHistoryItemActions } from './useHistoryItemActions.svelte';

  let { items = [], selectionState, on }: HistoryViewProps = $props();

  const isSelectionMode = $derived(selectionState?.isSelectionMode ?? false);

  const { handleItemClick, handleDelete, getItemState } = useHistoryItemActions(
    {
      getSelectionState: () => selectionState,
      onDelete: (id) => on?.delete(id),
      onCollectionChange: (imageId, collectionIds) =>
        on?.collectionChange(imageId, collectionIds),
      onSelectionChange: (id) => on?.selectionChange?.(id),
    },
  );
</script>

<Masonry
  {items}
  class="gap-4"
  columns={{
    xs: 1,
    sm: 2,
    md: 2,
    lg: 3,
    xl: 4,
  }}
  getItemWeight={calculateHistoryCardWeight}
>
  {#snippet itemTemplate(item)}
    <!-- svelte-ignore a11y_no_noninteractive_tabindex -->
    <article
      in:fly={{ y: 20, duration: 300, delay: Math.random() * 100 }}
      out:fade={{ duration: 200 }}
      class={cn(
        historyCardVariants({ selected: getItemState(item).isSelected }),
        isSelectionMode && 'cursor-pointer',
      )}
      onclick={(e) => isSelectionMode && handleItemClick(e, item)}
      onkeydown={(e) => {
        if (isSelectionMode && e.key === 'Enter') {
          e.preventDefault();
          selectionState?.toggle(item.id);
        }
      }}
      role={isSelectionMode ? 'button' : undefined}
      tabindex={isSelectionMode ? 0 : undefined}
    >
      <div class="relative">
        {#if isSelectionMode}
          <button
            type="button"
            onclick={(e) => handleItemClick(e, item)}
            class="absolute top-2 left-2 z-20"
            aria-label={getItemState(item).selectionAriaLabel}
          >
            <OverlayCheckbox selected={getItemState(item).isSelected} />
          </button>
        {/if}
        <a
          href={getItemState(item).itemHref || undefined}
          class={linkVariants({ selectionMode: isSelectionMode })}
        >
          <ImagePlaceholder
            uniqueId={item.id}
            src={`/image/${item.attributes.fileName}?width=400&height=400&crop=true`}
            metadata={item.metadata}
            showMetadata={false}
            showOpenInNewTab={false}
            rounded={false}
          />
        </a>

        <div class="absolute bottom-2 left-2 flex items-center gap-1.5">
          <VisibilityBadge
            isPublic={item.attributes.isPublic}
            variant="overlay"
            compact
          />
          <ViewCountBadge count={item.attributes.views} variant="overlay" />
        </div>

        <div
          class={actionBarVisibilityVariants({
            selectionMode: isSelectionMode ?? false,
          })}
        >
          <ImageActionBar
            image={createActionBarImage(item)}
            buttons={historyActionBarButtons}
            on={{
              imageDelete: handleDelete,
              collectionChange: (imageId, collectionIds) =>
                on?.collectionChange(imageId, collectionIds),
            }}
            compact
          />
        </div>
      </div>

      <div class="p-3">
        <div class="mb-2">
          <a
            href={`/info/${item.id}`}
            class="text-sm font-medium text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors truncate block"
            title={item.attributes.fileName}
          >
            {item.attributes.fileName}
          </a>
        </div>

        <div class="mb-3">
          <ImageMetadata {item} gap="sm" />
        </div>

        {#if (item.collections && item.collections.length > 0) || (item.tags && item.tags.length > 0)}
          <div class="space-y-2">
            {#if item.collections && item.collections.length > 0}
              <ImageCollectionList collections={item.collections} />
            {/if}

            {#if item.tags && item.tags.length > 0}
              <ImageTagList
                imageId={item.id}
                variant="neon"
                showImageCount={false}
                removable={false}
                initialTags={item.tags}
              />
            {/if}
          </div>
        {/if}
      </div>
    </article>
  {/snippet}
</Masonry>
