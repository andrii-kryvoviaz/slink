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
    class="w-10 h-10 rounded-md overflow-hidden bg-gray-100 dark:bg-gray-800/50 shrink-0 flex items-center justify-center"
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
          class="w-5 h-5 text-gray-400 dark:text-gray-600"
        />
      {/snippet}
    </LazyImage>
  </div>
  <span
    class="font-medium text-gray-900 dark:text-white truncate text-sm group-hover/name:text-blue-600 dark:group-hover/name:text-blue-400 transition-colors"
  >
    {@html collection.name}
  </span>
</a>
