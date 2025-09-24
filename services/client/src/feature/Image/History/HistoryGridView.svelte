<script lang="ts">
  import { ImageActionBar, ImagePlaceholder } from '@slink/feature/Image';
  import { Masonry } from '@slink/feature/Layout';
  import { ImageTagList } from '@slink/feature/Tag';
  import { FormattedDate } from '@slink/feature/Text';
  import { Button } from '@slink/ui/components/button';

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
</script>

<Masonry
  {items}
  class="gap-6"
  columns={{
    xs: 1,
    sm: 2,
    md: 2,
    lg: 3,
    xl: 4,
  }}
>
  {#snippet itemTemplate(item)}
    <article
      in:fly={{ y: 20, duration: 400, delay: Math.random() * 200 }}
      out:fade={{ duration: 500 }}
      class="group break-inside-avoid bg-white dark:bg-gray-900 rounded-2xl shadow-sm hover:shadow-xl border border-gray-100 dark:border-gray-800 overflow-hidden transition-all duration-300"
    >
      <div class="relative">
        <a href={`/info/${item.id}`} class="block">
          <ImagePlaceholder
            uniqueId={item.id}
            src={`/image/${item.attributes.fileName}?width=350&height=350&crop=true`}
            metadata={item.metadata}
            showMetadata={false}
            showOpenInNewTab={false}
            rounded={false}
          />
        </a>
      </div>

      <div class="p-4">
        <div class="flex items-start justify-between gap-2 mb-3">
          <Button
            href={`/info/${item.id}`}
            variant="link"
            class="group/link !p-0 text-sm font-medium text-gray-900 transition-colors dark:text-gray-100 flex-1 min-w-0"
          >
            <span class="truncate">{item.attributes.fileName}</span>
            <Icon
              icon="mynaui:external-link"
              class="ml-1 h-3 w-3 opacity-50 transition-opacity group-hover/link:opacity-100 shrink-0"
            />
          </Button>
        </div>

        <div class="w-full mb-3">
          <ImageActionBar
            image={{
              id: item.id,
              fileName: item.attributes.fileName,
              isPublic: item.attributes.isPublic,
            }}
            buttons={['download', 'visibility', 'copy', 'delete']}
            on={{ imageDelete: onImageDelete }}
          />
        </div>

        {#if item.tags && item.tags.length > 0}
          <div class="mb-3">
            <ImageTagList
              imageId={item.id}
              variant="minimal"
              showImageCount={false}
              removable={false}
              initialTags={item.tags}
            />
          </div>
        {/if}

        <div class="grid grid-cols-2 gap-2 text-xs">
          <div
            class="flex items-center gap-2 rounded-md bg-gray-50/50 px-2 py-1.5 dark:bg-gray-800/30"
          >
            <Icon
              icon="lucide:expand"
              class="h-3 w-3 text-gray-500 dark:text-gray-400"
            />
            <span class="text-gray-600 dark:text-gray-300 truncate">
              {item.metadata.width}×{item.metadata.height}
            </span>
          </div>

          <div
            class="flex items-center gap-2 rounded-md bg-gray-50/50 px-2 py-1.5 dark:bg-gray-800/30"
          >
            <Icon
              icon="lucide:hard-drive"
              class="h-3 w-3 text-gray-500 dark:text-gray-400"
            />
            <span class="text-gray-600 dark:text-gray-300 truncate">
              {bytesToSize(item.metadata.size)}
            </span>
          </div>

          <div
            class="flex items-center gap-2 rounded-md bg-gray-50/50 px-2 py-1.5 dark:bg-gray-800/30"
          >
            <Icon
              icon="lucide:file-type"
              class="h-3 w-3 text-gray-500 dark:text-gray-400"
            />
            <span class="text-gray-600 dark:text-gray-300 truncate">
              {item.metadata.mimeType}
            </span>
          </div>

          <div
            class="flex items-center gap-2 rounded-md bg-gray-50/50 px-2 py-1.5 dark:bg-gray-800/30"
          >
            <Icon
              icon="lucide:calendar"
              class="h-3 w-3 text-gray-500 dark:text-gray-400"
            />
            <div class="text-gray-600 dark:text-gray-300 truncate">
              <FormattedDate date={item.attributes.createdAt.timestamp} />
            </div>
          </div>
        </div>
      </div>
    </article>
  {/snippet}
</Masonry>
