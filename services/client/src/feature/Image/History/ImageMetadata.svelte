<script lang="ts">
  import { formatMimeType } from '@slink/feature/Image/utils/formatMimeType';
  import { FormattedDate } from '@slink/feature/Text';

  import { bytesToSize } from '$lib/utils/bytesConverter';
  import Icon from '@iconify/svelte';

  import type { ImageListingItem } from '@slink/api/Response';

  import {
    metadataContainerTheme,
    metadataDividerTheme,
    metadataIconTheme,
    metadataItemTheme,
  } from './ImageMetadata.theme';

  type Gap = 'sm' | 'md';

  interface Props {
    item: ImageListingItem;
    gap?: Gap;
  }

  let { item, gap = 'sm' }: Props = $props();
</script>

<div class="overflow-hidden">
  <div class={metadataContainerTheme({ gap })}>
    <span class={metadataItemTheme({ gap })} title="File type & dimensions">
      <Icon icon="lucide:image" class={metadataIconTheme({ gap })} />
      <span class="font-medium text-gray-600 dark:text-gray-300">
        {formatMimeType(item.metadata.mimeType)}
      </span>
      {item.metadata.width}×{item.metadata.height}
    </span>

    <span class="relative {metadataItemTheme({ gap })}" title="File size">
      <span class={metadataDividerTheme({ gap })}></span>
      <Icon icon="lucide:hard-drive" class={metadataIconTheme({ gap })} />
      {bytesToSize(item.metadata.size)}
    </span>

    {#if item.bookmarkCount > 0}
      <span class="relative {metadataItemTheme({ gap })}" title="Bookmarks">
        <span class={metadataDividerTheme({ gap })}></span>
        <Icon icon="lucide:bookmark" class={metadataIconTheme({ gap })} />
        {item.bookmarkCount.toLocaleString()}
      </span>
    {/if}

    <span class="relative {metadataItemTheme({ gap })}" title="Uploaded">
      <span class={metadataDividerTheme({ gap })}></span>
      <Icon icon="lucide:clock" class={metadataIconTheme({ gap })} />
      <FormattedDate date={item.attributes.createdAt.timestamp} />
    </span>
  </div>
</div>
