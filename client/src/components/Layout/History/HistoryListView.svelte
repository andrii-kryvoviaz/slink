<script lang="ts">
  import { createEventDispatcher } from 'svelte';

  import Icon from '@iconify/svelte';
  import { fade } from 'svelte/transition';

  import type { ImageListingItem } from '@slink/api/Response';

  import { bytesToSize } from '@slink/utils/bytesConverter';

  import { Button, FormattedDate } from '@slink/components/Common';
  import { ImageActionBar, ImagePlaceholder } from '@slink/components/Image';

  export let items: ImageListingItem[] = [];

  const dispatch = createEventDispatcher<{
    updateListing: ImageListingItem[];
  }>();

  const onImageDelete = ({ detail }: { detail: string }) => {
    listItems = items.filter((item) => item.id !== detail);
    dispatch('updateListing', listItems);
  };

  $: listItems = items;
</script>

<div class="mt-8 flex flex-col items-center gap-6">
  {#each listItems as item (item.id)}
    <div
      out:fade={{ duration: 500 }}
      class="image-container w-full max-w-full break-inside-avoid rounded-lg border bg-gray-200/5 p-4 dark:border-gray-800/50 sm:w-[48rem]"
    >
      <div class="flex items-center justify-between sm:mb-4">
        <div
          class="flex max-w-full flex-grow flex-col items-center gap-4 sm:flex-row sm:items-start"
        >
          <a
            href={`/info/${item.id}`}
            class="block w-full max-w-full flex-shrink sm:h-40 sm:w-40"
          >
            <ImagePlaceholder
              src={`/image/${item.attributes.fileName}?width=350&crop=true`}
              metadata={item.metadata}
              uniqueId={item.id}
              showOpenInNewTab={false}
              showMetadata={false}
              stretch={true}
              width={10}
              height={10}
            />
          </a>

          <div class="flex flex-grow flex-col gap-4">
            <div class="hidden justify-between sm:flex">
              <Button
                href={`/info/${item.id}`}
                variant="link"
                class=" p-0 text-sm font-light opacity-90 hover:opacity-100"
              >
                {item.attributes.fileName}
                <Icon icon="mynaui:external-link" class="ml-1" />
              </Button>
            </div>

            <div class="sm:mb-6">
              <ImageActionBar
                image={{
                  id: item.id,
                  fileName: item.attributes.fileName,
                  isPublic: item.attributes.isPublic,
                }}
                buttons={['download', 'visibility', 'copy', 'delete']}
                on:imageDeleted={onImageDelete}
              />
            </div>

            <div
              class="hidden flex-grow items-center justify-between gap-8 pr-8 sm:flex"
            >
              <div class="flex flex-col gap-1 text-xs">
                <p class="badge badge-primary badge-outline">
                  {item.metadata.mimeType}
                </p>
              </div>

              <div class="hidden text-center text-xs lg:block">
                <p class="font-semibold">Dimensions</p>
                <p>
                  {item.metadata.width}x{item.metadata.height} pixels
                </p>
              </div>

              <div class="hidden text-center text-xs lg:block">
                <p class="font-semibold">Size</p>
                <p>{bytesToSize(item.metadata.size)}</p>
              </div>

              <div class="hidden text-center text-xs lg:block">
                <p class="font-semibold">Uploaded on</p>
                <FormattedDate date={item.attributes.createdAt.timestamp} />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  {/each}
</div>
