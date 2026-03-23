<script lang="ts">
  import { StopPropagation } from '@slink/feature/Action';
  import {
    ImageActionBar,
    createActionBarImage,
    historyActionBarButtons,
  } from '@slink/feature/Image';

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

<StopPropagation>
  <div class="flex items-center justify-end">
    <ImageActionBar
      image={createActionBarImage(item)}
      buttons={historyActionBarButtons}
      on={{
        imageDelete: onDelete,
        collectionChange: (imageId, collections) =>
          onCollectionChange(imageId, collections),
        tagChange: (imageId, tags) => onTagChange?.(imageId, tags),
      }}
      compact
    />
  </div>
</StopPropagation>
