<script lang="ts">
  import * as Card from '@slink/ui/components/card';
  import type { Snippet } from 'svelte';

  import type { UploadItem } from '@slink/lib/services/upload.service';

  import { setUploadProgress } from './context.svelte';

  interface Props {
    items: UploadItem[];
    class?: string;
    children: Snippet;
  }

  let { items, class: className, children }: Props = $props();

  setUploadProgress(() => items);
</script>

<Card.Root fadeIn class={className}>
  {@render children()}
</Card.Root>
