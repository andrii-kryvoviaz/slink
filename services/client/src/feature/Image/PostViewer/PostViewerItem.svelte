<script lang="ts">
  import { ImagePlaceholder } from '@slink/feature/Image';

  import type { ImageListingItem } from '@slink/api/Response';

  import PostViewerSidebar from './PostViewerSidebar.svelte';

  interface Props {
    image: ImageListingItem;
    onClose?: () => void;
  }

  let { image, onClose }: Props = $props();
</script>

<div
  class="h-dvh w-full shrink-0 overflow-hidden snap-start snap-always"
  data-post-id={image.id}
>
  <div class="h-full w-full flex items-center justify-center p-4 lg:p-8">
    <div
      class="h-full w-full max-w-7xl flex flex-col lg:flex-row gap-4 lg:gap-8"
    >
      <div
        class="flex-1 flex items-center justify-center min-h-0 min-w-0 overflow-hidden"
      >
        <ImagePlaceholder
          src={`/image/${image.attributes.fileName}`}
          metadata={image.metadata}
          showMetadata={false}
          showOpenInNewTab={true}
          rounded={true}
          class="shadow-2xl max-h-full max-w-full object-contain"
        />
      </div>

      <div class="lg:hidden shrink-0 max-h-[40%] overflow-y-auto">
        <PostViewerSidebar {image} {onClose} compact />
      </div>

      <div class="hidden lg:flex w-96 shrink-0 h-full">
        <PostViewerSidebar {image} {onClose} />
      </div>
    </div>
  </div>
</div>
