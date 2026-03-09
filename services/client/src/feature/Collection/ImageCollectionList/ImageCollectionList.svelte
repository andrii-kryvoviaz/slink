<script lang="ts">
  import type { CollectionReference } from '@slink/api/Response/Collection/CollectionResponse';

  import { CollectionBadge } from '../CollectionBadge';

  interface Props {
    collections: CollectionReference[];
  }

  let { collections }: Props = $props();
</script>

{#if collections.length > 0}
  <div class="flex flex-wrap gap-2">
    {#each collections as collection (collection.id)}
      <CollectionBadge {collection} />
    {/each}
  </div>
{/if}
