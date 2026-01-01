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
  import { FormattedDate } from '@slink/feature/Text';

  import { bytesToSize } from '$lib/utils/bytesConverter';
  import Icon from '@iconify/svelte';
  import { fade, fly } from 'svelte/transition';

  import type { ImageListingItem } from '@slink/api/Response';

  interface Props {
    items?: ImageListingItem[];
    on?: {
      delete: (id: string) => void;
    };
  }

  let { items = [], on }: Props = $props();

  const onImageDelete = (id: string) => {
    on?.delete(id);
  };

  const formatMimeType = (mimeType: string): string => {
    const type = mimeType.split('/')[1];
    if (!type) return mimeType;
    return type.toUpperCase();
  };
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
    <article
      in:fly={{ y: 20, duration: 300, delay: Math.random() * 100 }}
      out:fade={{ duration: 200 }}
      class="group break-inside-avoid overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/60 transition-all duration-200 hover:border-gray-300 dark:hover:border-gray-700/80 hover:shadow-md dark:hover:shadow-gray-900/50"
    >
      <div class="relative">
        <a href={`/info/${item.id}`} class="block">
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
          class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200"
        >
          <ImageActionBar
            image={{
              id: item.id,
              fileName: item.attributes.fileName,
              isPublic: item.attributes.isPublic,
            }}
            buttons={['download', 'visibility', 'copy', 'delete']}
            on={{ imageDelete: onImageDelete }}
            compact={true}
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

        <div
          class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500 dark:text-gray-400 mb-3"
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
          <ImageTagList
            imageId={item.id}
            variant="neon"
            showImageCount={false}
            removable={false}
            initialTags={item.tags}
          />
        {/if}
      </div>
    </article>
  {/snippet}
</Masonry>
