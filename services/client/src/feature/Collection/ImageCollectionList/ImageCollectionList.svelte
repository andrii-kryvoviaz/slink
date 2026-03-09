<script lang="ts">
  import { OverflowBadgeList } from '@slink/feature/Layout/OverflowBadgeList';

  import type { CollectionReference } from '@slink/api/Response/Collection/CollectionResponse';

  import { CollectionBadge } from '../CollectionBadge';

  interface Props {
    collections: CollectionReference[];
    maxVisible?: number;
  }

  let { collections, maxVisible }: Props = $props();
</script>

{#if collections.length > 0}
  <OverflowBadgeList
    items={collections}
    {maxVisible}
    itemLabel="more collection"
  >
    {#snippet badge(collection)}
      <CollectionBadge {collection} />
    {/snippet}
  </OverflowBadgeList>
{/if}
