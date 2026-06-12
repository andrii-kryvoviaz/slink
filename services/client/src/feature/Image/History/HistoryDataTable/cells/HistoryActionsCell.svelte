<script lang="ts">
  import HistoryItemActions from '@slink/feature/Image/History/HistoryItemActions.svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';
  import type { ImageListingItem } from '@slink/api/Response';
  import type { CollectionReference } from '@slink/api/Response/Collection/CollectionResponse';

  interface Props {
    item: ImageListingItem;
    onDelete: (id: string) => void;
    onCollectionChange: (
      imageId: string,
      collections: CollectionReference[],
    ) => void;
    onTagChange?: (imageId: string, tags: Tag[]) => void;
  }

  let { item, onDelete, onCollectionChange, onTagChange }: Props = $props();
</script>

<HistoryItemActions
  {item}
  on={{
    imageDelete: onDelete,
    collectionChange: onCollectionChange,
    tagChange: (imageId, tags) => onTagChange?.(imageId, tags),
  }}
/>
