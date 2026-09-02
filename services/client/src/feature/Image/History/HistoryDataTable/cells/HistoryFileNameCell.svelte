<script lang="ts">
  import { Link } from '@slink/ui/components/link';

  import type { ImageListingItem } from '@slink/api/Response';

  interface Props {
    item: ImageListingItem;
  }

  let { item }: Props = $props();
</script>

<Link
  href="/info/{item.id}"
  class="text-sm font-medium text-foreground hover:text-accent transition-colors truncate max-w-[200px] inline-block"
  title={item.attributes.fileName}
>
  {item.attributes.fileName}
</Link>
