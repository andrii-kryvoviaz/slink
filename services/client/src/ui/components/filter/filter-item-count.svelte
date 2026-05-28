<script lang="ts">
  import type { Snippet } from 'svelte';

  import { cn } from '@slink/utils/ui/index.js';

  import { getFilterContainerContext } from './context.svelte';
  import { filterItemCountVariants } from './filter.theme';

  interface Props {
    class?: string;
    children?: Snippet;
  }

  let { class: className, children }: Props = $props();

  const container = getFilterContainerContext();
</script>

<span
  class={cn(filterItemCountVariants({ variant: container.variant }), className)}
>
  {@render children?.()}
</span>
