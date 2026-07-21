<script lang="ts">
  import { StopPropagation } from '@slink/feature/Action';
  import BookmarkButton from '@slink/feature/Image/BookmarkButton/BookmarkButton.svelte';
  import CopyLinkButton from '@slink/feature/Image/CopyLinkButton/CopyLinkButton.svelte';
  import DownloadButton from '@slink/feature/Image/DownloadButton/DownloadButton.svelte';

  import { page } from '$app/state';

  interface Props {
    image: {
      id: string;
      fileName: string;
      url: string;
      ownerId: string;
    };
    bookmark?: {
      isBookmarked?: boolean;
      count: number;
      onChange: (isBookmarked: boolean, count: number) => void;
    };
  }

  let { image, bookmark }: Props = $props();

  const isOwner = $derived(page.data.user?.id === image.ownerId);
</script>

<div
  class="absolute top-2 right-2 flex items-center gap-1.5 opacity-0 group-hover:opacity-100 focus-within:opacity-100 pointer-coarse:opacity-100 has-[[data-state=open]]:opacity-100 transition-opacity duration-200"
>
  <StopPropagation>
    <DownloadButton
      imageUrl={image.url}
      fileName={image.fileName}
      size="sm"
      variant="overlay"
    />
  </StopPropagation>
  {#if isOwner}
    <StopPropagation>
      <CopyLinkButton
        image={{ id: image.id, fileName: image.fileName }}
        variant="overlay"
      />
    </StopPropagation>
  {:else if bookmark}
    <StopPropagation>
      <BookmarkButton
        imageId={image.id}
        imageOwnerId={image.ownerId}
        isBookmarked={bookmark.isBookmarked}
        bookmarkCount={bookmark.count}
        size="sm"
        variant="overlay"
        onBookmarkChange={bookmark.onChange}
      />
    </StopPropagation>
  {/if}
</div>
