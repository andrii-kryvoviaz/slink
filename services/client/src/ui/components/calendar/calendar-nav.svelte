<script lang="ts">
  import type { HTMLAttributes } from 'svelte/elements';

  import { type WithElementRef, cn } from '@slink/utils/ui/index.js';

  let {
    ref = $bindable(null),
    class: className,
    children,
    ...restProps
  }: WithElementRef<HTMLAttributes<HTMLElement>> = $props();
</script>

<nav
  {...restProps}
  bind:this={ref}
  class={cn(
    'absolute inset-x-0 top-0 flex w-full items-center justify-between gap-1',
    className,
  )}
>
  {@render children?.()}
</nav>
