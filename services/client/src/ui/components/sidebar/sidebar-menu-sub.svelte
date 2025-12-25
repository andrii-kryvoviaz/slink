<script lang="ts">
  import type { HTMLAttributes } from 'svelte/elements';

  import { type WithElementRef, cn } from '@slink/utils/ui/index.js';

  let {
    ref = $bindable(null),
    class: className,
    children,
    ...restProps
  }: WithElementRef<HTMLAttributes<HTMLUListElement>> = $props();
</script>

<ul
  bind:this={ref}
  data-slot="sidebar-menu-sub"
  data-sidebar="menu-sub"
  class={cn(
    'mx-3.5 flex min-w-0 flex-col py-0.5 pl-0',
    'group-data-[collapsible=icon]:hidden',
    className,
  )}
  {...restProps}
>
  {@render children?.()}
</ul>
