<script lang="ts">
  import Badge from '@slink/feature/Text/Badge/Badge.svelte';

  import Icon from '@iconify/svelte';

  import type { CollectionReference } from '@slink/api/Response/Collection/CollectionResponse';

  interface Props {
    collection: CollectionReference;
  }

  let { collection }: Props = $props();
</script>

<a href="/collection/{collection.id}" class="shrink-0">
  <Badge variant="minimal" size="sm">
    <div class="flex items-center gap-1.5">
      <Icon icon="ph:folder-simple" class="h-3 w-3" />
      <span class="font-medium">{collection.name}</span>
    </div>
  </Badge>
</a>
