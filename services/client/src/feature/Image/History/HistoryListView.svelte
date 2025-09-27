<script lang="ts">
  import { ImageActionBar, ImagePlaceholder } from '@slink/feature/Image';
  import { ImageTagList } from '@slink/feature/Tag';
  import { FormattedDate } from '@slink/feature/Text';
  import { Button } from '@slink/ui/components/button';

  import { bytesToSize } from '$lib/utils/bytesConverter';
  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

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

<div class="flex flex-col gap-6 sm:gap-8">
  {#each items as item (item.id)}
    <div
      out:fade={{ duration: 500 }}
      class="group relative w-full overflow-hidden rounded-xl border border-gray-200/50 bg-white/80 backdrop-blur-sm transition-all duration-300 hover:shadow-xl hover:shadow-gray-200/60 hover:border-gray-300/70 hover:bg-white/95 dark:border-gray-700/50 dark:bg-gray-900/80 dark:hover:shadow-gray-900/40 dark:hover:border-gray-600/70 dark:hover:bg-gray-800/95"
    >
      <div class="p-4 sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:gap-6 lg:gap-8">
          <a
            href={`/info/${item.id}`}
            class="flex w-full shrink-0 overflow-hidden rounded-t-xl max-w-full sm:rounded-lg sm:w-96 lg:w-80 xl:w-96 sm:max-w-none"
          >
            <ImagePlaceholder
              src={`/image/${item.attributes.fileName}?width=350&height=350&crop=true`}
              metadata={item.metadata}
              uniqueId={item.id}
              showOpenInNewTab={false}
              showMetadata={false}
            />
          </a>

          <div class="flex flex-col gap-4 min-w-0 flex-1">
            <div class="block sm:hidden">
              <Button
                href={`/info/${item.id}`}
                variant="link"
                class="group/link !p-0 text-base font-medium text-gray-900 transition-colors dark:text-gray-100 w-full justify-start min-w-0"
              >
                <span class="truncate">{item.attributes.fileName}</span>
                <Icon
                  icon="mynaui:external-link"
                  class="ml-2 h-4 w-4 opacity-50 transition-opacity group-hover/link:opacity-100 shrink-0"
                />
              </Button>
            </div>

            <div class="hidden sm:block">
              <Button
                href={`/info/${item.id}`}
                variant="link"
                class="group/link !p-0 text-base font-medium text-gray-900 transition-colors dark:text-gray-100 min-w-0"
              >
                <span class="truncate">{item.attributes.fileName}</span>
                <Icon
                  icon="mynaui:external-link"
                  class="ml-2 h-4 w-4 opacity-50 transition-opacity group-hover/link:opacity-100"
                />
              </Button>
            </div>

            <div class="w-full">
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
              <div class="mt-4">
                <div class="flex items-center gap-2 mb-2">
                  <Icon
                    icon="ph:tag"
                    class="h-4 w-4 text-gray-500 dark:text-gray-400"
                  />
                  <span
                    class="text-xs font-medium text-gray-500 dark:text-gray-500"
                  >
                    Tags
                  </span>
                </div>
                <ImageTagList
                  imageId={item.id}
                  variant="neon"
                  showImageCount={false}
                  removable={false}
                  initialTags={item.tags}
                />
              </div>
            {/if}

            <div
              class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2 transition-opacity duration-300 group-hover:opacity-100 opacity-90"
            >
              <div
                class="flex items-center gap-3 rounded-md bg-gray-50/30 px-3 py-2 dark:bg-gray-800/20 transition-colors duration-300 group-hover:bg-gray-100/50 dark:group-hover:bg-gray-700/30"
              >
                <div
                  class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100/50 dark:bg-gray-700/30 shrink-0"
                >
                  <Icon
                    icon="lucide:file-type"
                    class="h-4 w-4 text-gray-600 dark:text-gray-400"
                  />
                </div>
                <div class="flex flex-col min-w-0 flex-1">
                  <span
                    class="text-xs font-medium text-gray-500 dark:text-gray-500"
                  >
                    Type
                  </span>
                  <span
                    class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate"
                  >
                    {item.metadata.mimeType}
                  </span>
                </div>
              </div>

              <div
                class="flex items-center gap-3 rounded-md bg-gray-50/30 px-3 py-2 dark:bg-gray-800/20 transition-colors duration-300 group-hover:bg-gray-100/50 dark:group-hover:bg-gray-700/30"
              >
                <div
                  class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100/50 dark:bg-gray-700/30 shrink-0"
                >
                  <Icon
                    icon="lucide:expand"
                    class="h-4 w-4 text-gray-600 dark:text-gray-400"
                  />
                </div>
                <div class="flex flex-col min-w-0 flex-1">
                  <span
                    class="text-xs font-medium text-gray-500 dark:text-gray-500"
                  >
                    Dimensions
                  </span>
                  <span
                    class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate"
                  >
                    {item.metadata.width}×{item.metadata.height}
                  </span>
                </div>
              </div>

              <div
                class="flex items-center gap-3 rounded-md bg-gray-50/30 px-3 py-2 dark:bg-gray-800/20 transition-colors duration-300 group-hover:bg-gray-100/50 dark:group-hover:bg-gray-700/30"
              >
                <div
                  class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100/50 dark:bg-gray-700/30 shrink-0"
                >
                  <Icon
                    icon="lucide:hard-drive"
                    class="h-4 w-4 text-gray-600 dark:text-gray-400"
                  />
                </div>
                <div class="flex flex-col min-w-0 flex-1">
                  <span
                    class="text-xs font-medium text-gray-500 dark:text-gray-500"
                  >
                    Size
                  </span>
                  <span
                    class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate"
                  >
                    {bytesToSize(item.metadata.size)}
                  </span>
                </div>
              </div>

              <div
                class="flex items-center gap-3 rounded-md bg-gray-50/30 px-3 py-2 dark:bg-gray-800/20 transition-colors duration-300 group-hover:bg-gray-100/50 dark:group-hover:bg-gray-700/30"
              >
                <div
                  class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100/50 dark:bg-gray-700/30 shrink-0"
                >
                  <Icon
                    class="h-4 w-4 text-gray-600 dark:text-gray-400"
                    icon="lucide:calendar"
                  />
                </div>
                <div class="flex flex-col min-w-0 flex-1">
                  <span
                    class="text-xs font-medium text-gray-500 dark:text-gray-500"
                  >
                    Uploaded
                  </span>
                  <div
                    class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate"
                  >
                    <FormattedDate date={item.attributes.createdAt.timestamp} />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  {/each}
</div>
