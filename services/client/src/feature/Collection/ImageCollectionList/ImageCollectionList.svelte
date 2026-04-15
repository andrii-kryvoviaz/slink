<script lang="ts">
  import { OverflowBadgeList } from '@slink/feature/Layout/OverflowBadgeList';

  import { plural } from '$lib/utils/i18n';

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
    overflowLabel={(count) =>
      plural(count, ['# more collection', '# more collections'])}
  >
    {#snippet badge(collection)}
      <CollectionBadge {collection} />
    {/snippet}
  </OverflowBadgeList>
{/if}
