<script lang="ts">
  import { CollectionActionsDropdown } from '@slink/feature/Collection';

  import type { CollectionResponse } from '@slink/api/Response';

  interface Props {
    collection: CollectionResponse;
  }

  let { collection }: Props = $props();
</script>

<div class="flex items-center justify-end">
  <CollectionActionsDropdown {collection} />
</div>
