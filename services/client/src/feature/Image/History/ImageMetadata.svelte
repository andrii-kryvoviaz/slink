<script lang="ts">
  import { formatMimeType } from '@slink/feature/Image/utils/formatMimeType';
  import { FormattedDate } from '@slink/feature/Text';

  import { bytesToSize } from '$lib/utils/bytesConverter';
  import Icon from '@iconify/svelte';

  import type { ImageListingItem } from '@slink/api/Response';

  type Gap = 'sm' | 'md';

  interface Props {
    item: ImageListingItem;
    gap?: Gap;
  }

  let { item, gap = 'sm' }: Props = $props();

  const gapClasses: Record<Gap, string> = {
    sm: 'gap-x-2',
    md: 'gap-x-3',
  };
</script>

<div
  class="flex flex-wrap items-center gap-y-1 text-xs text-gray-500 dark:text-gray-400 {gapClasses[
    gap
  ]}"
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
