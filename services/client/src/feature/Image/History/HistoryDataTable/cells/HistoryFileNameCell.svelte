<script lang="ts">
  import type { ImageListingItem } from '@slink/api/Response';

  interface Props {
    item: ImageListingItem;
  }

  let { item }: Props = $props();
</script>

<a
  href="/info/{item.id}"
  class="text-sm font-medium text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors truncate max-w-[200px] inline-block"
  title={item.attributes.fileName}
>
  {item.attributes.fileName}
</a>
