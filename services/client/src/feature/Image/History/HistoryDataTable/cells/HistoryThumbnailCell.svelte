<script lang="ts">
  import { ImagePlaceholder } from '@slink/feature/Image';

  import type { ImageListingItem } from '@slink/api/Response';

  interface Props {
    item: ImageListingItem;
  }

  let { item }: Props = $props();
</script>

<a
  href="/info/{item.id}"
  class="block w-12 h-12 rounded-md overflow-hidden shrink-0"
>
  <ImagePlaceholder
    src={`/image/${item.attributes.fileName}?width=96&height=96&crop=true`}
    metadata={item.metadata}
    uniqueId={item.id}
    showOpenInNewTab={false}
    showMetadata={false}
    keepAspectRatio={false}
    objectFit="cover"
    rounded={false}
    class="h-full w-full"
  />
</a>
