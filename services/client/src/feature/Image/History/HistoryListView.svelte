<script lang="ts">
  import { StopPropagation } from '@slink/feature/Action';
  import { ImageCollectionList } from '@slink/feature/Collection';
  import {
    ImageActionBar,
    ImagePlaceholder,
    ViewCountBadge,
    VisibilityBadge,
  } from '@slink/feature/Image';
  import { ImageTagList } from '@slink/feature/Tag';
  import { FormattedDate } from '@slink/feature/Text';
  import { OverlayCheckbox } from '@slink/ui/components/checkbox';
  import { Link } from '@slink/ui/components/link';

  import { fade, fly } from 'svelte/transition';

  import { cn } from '@slink/utils/ui';

  import {
    checkboxVariants,
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

  const { handleSelect, handleKeydown, handleDelete, getItemState } =
    useHistoryItemActions({
      getSelectionState: () => selectionState,
      onDelete: (id) => on?.delete(id),
      onCollectionChange: (imageId, collections) =>
        on?.collectionChange(imageId, collections),
      onSelectionChange: (id) => on?.selectionChange?.(id),
    });
</script>

<ul class="flex flex-col gap-3" role="list">
  {#each items as item, index (item.id)}
    <li
      in:fly={{ y: 20, duration: 300, delay: index * 50 }}
      out:fade={{ duration: 200 }}
    >
      <!-- svelte-ignore a11y_no_noninteractive_element_to_interactive_role -->
      <article
        class={cn(
          historyListRowVariants({
            selected: getItemState(item).isSelected,
            selectionMode: isSelectionMode ?? false,
          }),
          'cursor-pointer',
        )}
        onclick={(e) => handleSelect(e, item)}
        onkeydown={(e) => handleKeydown(e, item)}
        role="button"
        tabindex={0}
      >
        <div
          class="relative block w-full sm:w-40 md:w-48 lg:w-56 shrink-0 overflow-hidden bg-gray-100 dark:bg-gray-800/80"
        >
          <button
            type="button"
            onclick={(e) => {
              e.stopPropagation();
              handleSelect(e, item);
            }}
            class={cn(
              checkboxVariants({ selectionMode: isSelectionMode }),
              'pointer-events-auto',
            )}
            aria-label={getItemState(item).selectionAriaLabel}
          >
            <OverlayCheckbox selected={getItemState(item).isSelected} />
          </button>
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
        </div>

        <div class="flex flex-col flex-1 p-3 sm:p-4 min-w-0">
          <div class="flex items-start justify-between gap-3 mb-2">
            <Link
              href={`/info/${item.id}`}
              class="text-base font-medium text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors truncate"
              title={item.attributes.fileName}
            >
              {item.attributes.fileName}
            </Link>

            <StopPropagation>
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
                    collectionChange: (imageId, collections) =>
                      on?.collectionChange(imageId, collections),
                    tagChange: (imageId, tags) =>
                      on?.tagChange?.(imageId, tags),
                  }}
                  compact
                />
              </div>
            </StopPropagation>
          </div>

          <div class="mb-3">
            <ImageMetadata {item} gap="md" />
          </div>

          <div class="mt-auto flex flex-col gap-1">
            {#if item.tags && item.tags.length > 0}
              <ImageTagList
                imageId={item.id}
                variant="neon"
                showImageCount={false}
                removable={false}
                initialTags={item.tags}
                maxVisible={5}
              />
            {:else}
              <div class="text-xs text-gray-400 dark:text-gray-600 sm:hidden">
                <FormattedDate date={item.attributes.createdAt.timestamp} />
              </div>
            {/if}

            {#if item.collections && item.collections.length > 0}
              <ImageCollectionList
                collections={item.collections}
                maxVisible={5}
              />
            {/if}
          </div>
        </div>
      </article>
    </li>
  {/each}
</ul>
