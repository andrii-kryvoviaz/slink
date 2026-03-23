<script lang="ts">
  import { StopPropagation } from '@slink/feature/Action';
  import { ImageCollectionList } from '@slink/feature/Collection';
  import {
    ImageActionBar,
    ImagePlaceholder,
    ViewCountBadge,
    VisibilityBadge,
  } from '@slink/feature/Image';
  import { calculateHistoryCardWeight } from '@slink/feature/Image/utils/calculateHistoryCardWeight';
  import { Masonry } from '@slink/feature/Layout';
  import { ImageTagList } from '@slink/feature/Tag';
  import { OverlayCheckbox } from '@slink/ui/components/checkbox';
  import { Link } from '@slink/ui/components/link';

  import { fade, fly } from 'svelte/transition';

  import { cn } from '@slink/utils/ui';

  import {
    actionBarVisibilityVariants,
    checkboxVariants,
    createActionBarImage,
    historyActionBarButtons,
    historyCardVariants,
  } from './HistoryView.theme';
  import type { HistoryViewProps } from './HistoryView.types';
  import ImageMetadata from './ImageMetadata.svelte';
  import { useHistoryItemActions } from './useHistoryItemActions.svelte';

  let { items = [], selectionState, on }: HistoryViewProps = $props();

  const isSelectionMode = $derived(selectionState?.isSelectionMode ?? false);

  const { handleSelect, handleKeydown, handleDelete, getItemState } =
    useHistoryItemActions({
      getSelectionState: () => selectionState,
      onDelete: (id) => on?.delete(id),
      onCollectionChange: (imageId, collections) =>
        on?.collectionChange(imageId, collections),
      onSelectionChange: (id) => on?.selectionChange?.(id),
    });
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
    <!-- svelte-ignore a11y_no_noninteractive_element_to_interactive_role -->
    <article
      in:fly={{ y: 20, duration: 300, delay: Math.random() * 100 }}
      out:fade={{ duration: 200 }}
      class={cn(
        historyCardVariants({ selected: getItemState(item).isSelected }),
        'cursor-pointer',
      )}
      onclick={(e) => handleSelect(e, item)}
      onkeydown={(e) => handleKeydown(e, item)}
      role="button"
      tabindex={0}
    >
      <div class="relative">
        <button
          type="button"
          onclick={(e) => {
            e.stopPropagation();
            handleSelect(e, item);
          }}
          class={checkboxVariants({ selectionMode: isSelectionMode })}
          aria-label={getItemState(item).selectionAriaLabel}
        >
          <OverlayCheckbox selected={getItemState(item).isSelected} />
        </button>
        <div>
          <ImagePlaceholder
            uniqueId={item.id}
            src={`/image/${item.attributes.fileName}?width=400&height=400&crop=true`}
            metadata={item.metadata}
            showMetadata={false}
            showOpenInNewTab={false}
            rounded={false}
          />
        </div>

        <div class="absolute bottom-2 left-2 flex items-center gap-1.5">
          <VisibilityBadge
            isPublic={item.attributes.isPublic}
            variant="overlay"
            compact
          />
          <ViewCountBadge count={item.attributes.views} variant="overlay" />
        </div>

        <StopPropagation>
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
                collectionChange: (imageId, collections) =>
                  on?.collectionChange(imageId, collections),
                tagChange: (imageId, tags) => on?.tagChange?.(imageId, tags),
              }}
              compact
            />
          </div>
        </StopPropagation>
      </div>

      <div class="p-3">
        <div class="mb-2">
          <Link
            href={`/info/${item.id}`}
            class="text-sm font-medium text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors truncate block"
            title={item.attributes.fileName}
          >
            {item.attributes.fileName}
          </Link>
        </div>

        <div class="mb-3">
          <ImageMetadata {item} gap="sm" />
        </div>

        <div class="flex flex-col gap-1">
          {#if item.tags && item.tags.length > 0}
            <ImageTagList
              imageId={item.id}
              variant="neon"
              showImageCount={false}
              removable={false}
              initialTags={item.tags}
              maxVisible={3}
            />
          {/if}
          {#if item.collections && item.collections.length > 0}
            <ImageCollectionList
              collections={item.collections}
              maxVisible={3}
            />
          {/if}
        </div>
      </div>
    </article>
  {/snippet}
</Masonry>
