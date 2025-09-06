<script lang="ts">
  import type { HTMLAttributes } from 'svelte/elements';

  import { type WithElementRef, cn } from '@slink/utils/ui/index.js';

  let {
    ref = $bindable(null),
    class: className,
    children,
    ...restProps
  }: WithElementRef<HTMLAttributes<HTMLSpanElement>> = $props();
</script>

<span
  bind:this={ref}
  data-slot="context-menu-shortcut"
  class={cn('text-muted-foreground ml-auto text-xs tracking-widest', className)}
  {...restProps}
>
  {@render children?.()}
</span>
