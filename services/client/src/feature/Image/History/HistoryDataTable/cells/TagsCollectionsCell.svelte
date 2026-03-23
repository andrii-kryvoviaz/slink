<script lang="ts">
  import { ImageCollectionList } from '@slink/feature/Collection';
  import { ImageTagList } from '@slink/feature/Tag';

  import type { ImageListingItem } from '@slink/api/Response';

  interface Props {
    item: ImageListingItem;
  }

  let { item }: Props = $props();
</script>

<div class="flex flex-col gap-1">
  {#if item.tags && item.tags.length > 0}
    <ImageTagList
      imageId={item.id}
      variant="neon"
      showImageCount={false}
      removable={false}
      initialTags={item.tags}
      maxVisible={3}
      disableHover
    />
  {/if}
  {#if item.collections && item.collections.length > 0}
    <ImageCollectionList collections={item.collections} maxVisible={3} />
  {/if}
</div>
