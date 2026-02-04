<script lang="ts">
  import {
    ImageActionBar,
    ImagePlaceholder,
    ViewCountBadge,
    VisibilityBadge,
  } from '@slink/feature/Image';
  import { ImageTagList } from '@slink/feature/Tag';
  import { FormattedDate } from '@slink/feature/Text';
  import { OverlayCheckbox } from '@slink/ui/components/checkbox';

  import { fade, fly } from 'svelte/transition';

  import { cn } from '@slink/utils/ui';

  import {
    createActionBarImage,
    historyActionBarButtons,
    historyListRowVariants,
    listActionBarVisibilityVariants,
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

<ul class="flex flex-col gap-3" role="list">
  {#each items as item, index (item.id)}
    <li
      in:fly={{ y: 20, duration: 300, delay: index * 50 }}
      out:fade={{ duration: 200 }}
    >
      <!-- svelte-ignore a11y_no_noninteractive_tabindex -->
      <article
        class={cn(
          historyListRowVariants({
            selected: getItemState(item).isSelected,
            selectionMode: isSelectionMode ?? false,
          }),
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
        <a
          href={getItemState(item).itemHref || undefined}
          class={cn(
            'relative block w-full sm:w-40 md:w-48 lg:w-56 shrink-0 overflow-hidden bg-gray-100 dark:bg-gray-800/80',
            isSelectionMode && 'pointer-events-none',
          )}
          aria-label={`View ${item.attributes.fileName}`}
          onclick={(e) => handleItemClick(e, item)}
        >
          {#if isSelectionMode}
            <button
              type="button"
              onclick={(e) => handleItemClick(e, item)}
              class="absolute top-2 left-2 z-20 pointer-events-auto"
              aria-label={getItemState(item).selectionAriaLabel}
            >
              <OverlayCheckbox selected={getItemState(item).isSelected} />
            </button>
          {/if}
          <div class="aspect-4/3 sm:aspect-square w-full h-full">
            <ImagePlaceholder
              src={`/image/${item.attributes.fileName}?width=300&height=300&crop=true`}
              metadata={item.metadata}
              uniqueId={item.id}
              showOpenInNewTab={false}
              showMetadata={false}
              keepAspectRatio={false}
              objectFit="cover"
              rounded={false}
              class="h-full w-full transition-transform duration-300 group-hover:scale-105"
            />
          </div>

          <div class="absolute bottom-2 left-2 flex items-center gap-1.5">
            <VisibilityBadge
              isPublic={item.attributes.isPublic}
              variant="overlay"
            />
            <ViewCountBadge count={item.attributes.views} variant="overlay" />
          </div>
        </a>

        <div class="flex flex-col flex-1 p-3 sm:p-4 min-w-0">
          <div class="flex items-start justify-between gap-3 mb-2">
            <a
              href={`/info/${item.id}`}
              class="text-base font-medium text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors truncate"
              title={item.attributes.fileName}
            >
              {item.attributes.fileName}
            </a>

            <div
              class={listActionBarVisibilityVariants({
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

          <div class="mb-3">
            <ImageMetadata {item} gap="md" />
          </div>

          {#if item.tags && item.tags.length > 0}
            <div class="mt-auto">
              <ImageTagList
                imageId={item.id}
                variant="neon"
                showImageCount={false}
                removable={false}
                initialTags={item.tags}
              />
            </div>
          {:else}
            <div
              class="mt-auto text-xs text-gray-400 dark:text-gray-600 sm:hidden"
            >
              <FormattedDate date={item.attributes.createdAt.timestamp} />
            </div>
          {/if}
        </div>
      </article>
    </li>
  {/each}
</ul>
