<script lang="ts">
  import { StopPropagation } from '@slink/feature/Action';
  import { ImageActionBar } from '@slink/feature/Image';

  import type { Tag } from '@slink/api/Resources/TagResource';
  import type { ImageListingItem } from '@slink/api/Response';
  import type { CollectionReference } from '@slink/api/Response/Collection/CollectionResponse';

  import HistoryActionsMenu from './HistoryActionsMenu.svelte';
  import {
    createActionBarImage,
    historyActionBarButtons,
    historyItemActionsVariants,
  } from './HistoryView.theme';

  interface Props {
    item: ImageListingItem;
    layout?: 'table' | 'list';
    hoverReveal?: boolean;
    selectionMode?: boolean;
    on: {
      imageDelete?: (imageId: string) => void;
      collectionChange?: (
        imageId: string,
        collections: CollectionReference[],
      ) => void;
      tagChange?: (imageId: string, tags: Tag[]) => void;
    };
  }

  let {
    item,
    layout = 'table',
    hoverReveal = false,
    selectionMode = false,
    on,
  }: Props = $props();

  const image = $derived(createActionBarImage(item));
  const theme = $derived(
    historyItemActionsVariants({ layout, hoverReveal, selectionMode }),
  );
</script>

<StopPropagation>
  <div class={theme.bar()}>
    <ImageActionBar {image} buttons={historyActionBarButtons} {on} compact />
  </div>
  <div class={theme.menu()}>
    <HistoryActionsMenu {image} {on} />
  </div>
</StopPropagation>
