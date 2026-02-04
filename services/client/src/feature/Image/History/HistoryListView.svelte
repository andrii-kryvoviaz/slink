<script lang="ts">
  import {
    ImageActionBar,
    ImagePlaceholder,
    ViewCountBadge,
    VisibilityBadge,
  } from '@slink/feature/Image';
  import { ImageTagList } from '@slink/feature/Tag';
  import { FormattedDate } from '@slink/feature/Text';

  import { bytesToSize } from '$lib/utils/bytesConverter';
  import Icon from '@iconify/svelte';
  import { fade, fly } from 'svelte/transition';

  import type { ImageListingItem } from '@slink/api/Response';

  import type { ImageSelectionState } from '@slink/lib/state/ImageSelectionState.svelte';

  import { cn } from '@slink/utils/ui';

  import {
    historyListRowVariants,
    listActionBarVisibilityVariants,
    selectionCheckboxVariants,
  } from './HistoryView.theme';

  interface Props {
    items?: ImageListingItem[];
    selectionState?: ImageSelectionState;
    on?: {
      delete: (id: string) => void;
      collectionChange: (imageId: string, collectionIds: string[]) => void;
      selectionChange?: (id: string) => void;
    };
  }

  let { items = [], selectionState, on }: Props = $props();

  const onImageDelete = (id: string) => {
    on?.delete(id);
  };

  const formatMimeType = (mimeType: string): string => {
    const type = mimeType.split('/')[1];
    if (!type) return mimeType;
    return type.toUpperCase();
  };

  const handleRowClick = (e: MouseEvent, item: ImageListingItem) => {
    if (selectionState?.isSelectionMode) {
      e.preventDefault();
      selectionState.toggle(item.id);
      on?.selectionChange?.(item.id);
    }
  };
</script>

<ul class="flex flex-col gap-3" role="list">
  {#each items as item, index (item.id)}
    {@const isSelected = selectionState?.isSelected(item.id) ?? false}
    {@const isSelectionMode = selectionState?.isSelectionMode ?? false}
    <li
      in:fly={{ y: 20, duration: 300, delay: index * 50 }}
      out:fade={{ duration: 200 }}
    >
      <article
        class={historyListRowVariants({
          selected: isSelected,
          selectionMode: isSelectionMode,
        })}
        onclick={(e) => handleRowClick(e, item)}
        role={isSelectionMode ? 'button' : undefined}
        tabindex={isSelectionMode ? 0 : undefined}
      >
        <a
          href={isSelectionMode ? undefined : `/info/${item.id}`}
          class={cn(
            'relative block w-full sm:w-40 md:w-48 lg:w-56 shrink-0 overflow-hidden bg-gray-100 dark:bg-gray-800/80',
            isSelectionMode && 'pointer-events-none',
          )}
          aria-label={`View ${item.attributes.fileName}`}
        >
          {#if isSelectionMode}
            <div class="absolute top-2 left-2 z-20">
              <div class={selectionCheckboxVariants({ selected: isSelected })}>
                {#if isSelected}
                  <Icon icon="heroicons:check" class="w-4 h-4 text-white" />
                {/if}
              </div>
            </div>
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
                selectionMode: isSelectionMode,
              })}
            >
              <ImageActionBar
                image={{
                  id: item.id,
                  fileName: item.attributes.fileName,
                  isPublic: item.attributes.isPublic,
                  collectionIds: item.collectionIds,
                }}
                buttons={[
                  'download',
                  'collection',
                  'copy',
                  'visibility',
                  'delete',
                ]}
                on={{
                  imageDelete: onImageDelete,
                  collectionChange: on?.collectionChange,
                }}
                compact={true}
              />
            </div>
          </div>

          <div
            class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500 dark:text-gray-400 mb-3"
          >
            <span class="inline-flex items-center gap-1" title="File type">
              <Icon icon="lucide:file" class="w-3 h-3" />
              {formatMimeType(item.metadata.mimeType)}
            </span>
            <span class="text-gray-300 dark:text-gray-700">•</span>
            <span class="inline-flex items-center gap-1" title="Dimensions">
              <Icon icon="lucide:maximize-2" class="w-3 h-3" />
              {item.metadata.width}×{item.metadata.height}
            </span>
            <span class="text-gray-300 dark:text-gray-700">•</span>
            <span class="inline-flex items-center gap-1" title="File size">
              <Icon icon="lucide:database" class="w-3 h-3" />
              {bytesToSize(item.metadata.size)}
            </span>
            {#if item.bookmarkCount > 0}
              <span class="text-gray-300 dark:text-gray-700">•</span>
              <span class="inline-flex items-center gap-1" title="Bookmarks">
                <Icon icon="lucide:bookmark" class="w-3 h-3" />
                {item.bookmarkCount.toLocaleString()}
              </span>
            {/if}
            <span class="text-gray-300 dark:text-gray-700">•</span>
            <span class="inline-flex items-center gap-1" title="Uploaded">
              <Icon icon="lucide:clock" class="w-3 h-3" />
              <FormattedDate date={item.attributes.createdAt.timestamp} />
            </span>
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
