<script lang="ts">
  import { ImagePlaceholder } from '@slink/feature/Image';

  import type {
    AuthenticatedUser,
    ImageListingItem,
  } from '@slink/api/Response';

  import PostViewerSidebar from './PostViewerSidebar.svelte';

  interface Props {
    image: ImageListingItem;
    currentUser: AuthenticatedUser | null;
    isActive?: boolean;
    onClose?: () => void;
  }

  let { image, currentUser, isActive = false, onClose }: Props = $props();
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
        class="shrink-0 lg:flex-1 flex items-start lg:items-center justify-center min-w-0 overflow-hidden max-h-[35%] lg:max-h-none lg:min-h-0"
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

      <div class="flex-1 lg:w-96 lg:flex-none min-h-0 overflow-hidden">
        <PostViewerSidebar {image} {currentUser} {onClose} {isActive} />
      </div>
    </div>
  </div>
</div>
