<script lang="ts">
  import { LazyImage } from '@slink/ui/components/lazy-image';

  import Icon from '@iconify/svelte';

  import type { CollectionResponse } from '@slink/api/Response';

  interface Props {
    collection: CollectionResponse;
  }

  let { collection }: Props = $props();
</script>

<a
  href="/collection/{collection.id}"
  class="flex items-center gap-3 min-w-0 group/name"
>
  <div
    class="w-10 h-10 rounded-md overflow-hidden bg-muted shrink-0 flex items-center justify-center"
  >
    <LazyImage
      src={collection.coverImage}
      alt={collection.name}
      class="w-full h-full object-cover"
      containerClass="w-full h-full"
    >
      {#snippet placeholder()}
        <Icon
          icon="ph:folder-simple-duotone"
          class="w-5 h-5 text-foreground-subtle"
        />
      {/snippet}
    </LazyImage>
  </div>
  <span
    class="font-medium text-foreground truncate text-sm group-hover/name:text-info transition-colors"
  >
    {collection.name}
  </span>
</a>
