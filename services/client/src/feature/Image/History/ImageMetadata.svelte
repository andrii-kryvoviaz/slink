<script lang="ts">
  import { formatMimeType } from '@slink/feature/Image/utils/formatMimeType';
  import { FormattedDate } from '@slink/feature/Text';

  import { bytesToSize } from '$lib/utils/bytesConverter';
  import { plural } from '$lib/utils/i18n';

  import type { ImageListingItem } from '@slink/api/Response';

  import { metadataContainerTheme } from './ImageMetadata.theme';

  type Gap = 'sm' | 'md';

  interface Props {
    item: ImageListingItem;
    gap?: Gap;
  }

  let { item, gap = 'sm' }: Props = $props();
</script>

<div class={metadataContainerTheme({ gap })}>
  <span class="font-semibold text-muted-foreground">
    {formatMimeType(item.metadata.mimeType)}
  </span>
  <span>{item.metadata.width}×{item.metadata.height}</span>
  <span>{bytesToSize(item.metadata.size)}</span>
  {#if item.bookmarkCount > 0}
    <span>{plural(item.bookmarkCount, ['# bookmark', '# bookmarks'])}</span>
  {/if}
  <FormattedDate date={item.attributes.createdAt.timestamp} />
</div>
