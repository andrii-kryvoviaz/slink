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
      class="image-container w-[48rem] max-w-full break-inside-avoid rounded-lg border bg-gray-200/5 p-4 dark:border-gray-800/50"
    >
      <div class="mb-4 flex items-center justify-between">
        <div class="flex flex-grow gap-4">
          <a href={`/info/${item.id}`}>
            <ImagePlaceholder
              src={`/image/${item.attributes.fileName}?width=160&crop=true`}
              metadata={item.metadata}
              uniqueId={item.id}
              showOpenInNewTab={false}
              showMetadata={false}
              width={10}
              height={10}
            />
          </a>

          <div class="flex flex-grow flex-col gap-4">
            <div class="flex justify-between">
              <Button
                href={`/info/${item.id}`}
                variant="link"
                class=" p-0 text-sm font-light opacity-90 hover:opacity-100"
              >
                {item.attributes.fileName}
                <Icon icon="mynaui:external-link" class="ml-1" />
              </Button>
            </div>

            <ImageActionBar
              image={{
                id: item.id,
                fileName: item.attributes.fileName,
                isPublic: item.attributes.isPublic,
              }}
              buttons={['download', 'visibility', 'copy', 'delete']}
              on:imageDeleted={onImageDelete}
            />

            <div class="flex flex-grow items-center justify-between gap-8 pr-8">
              <div class="flex flex-col gap-1 text-xs">
                <p class="badge badge-primary badge-outline">
                  {item.metadata.mimeType}
                </p>
              </div>

              <div class="text-center text-xs">
                <p class=" font-semibold">Dimensions</p>
                <p>
                  {item.metadata.width}x{item.metadata.height} pixels
                </p>
              </div>

              <div class="text-center text-xs">
                <p class="font-semibold">Size</p>
                <p>{bytesToSize(item.metadata.size)}</p>
              </div>

              <div class="text-center text-xs">
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
