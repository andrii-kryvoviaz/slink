<script lang="ts">
  import type { HTMLThAttributes } from 'svelte/elements';

  import { type WithElementRef, cn } from '@slink/utils/ui/index.js';

  let {
    ref = $bindable(null),
    class: className,
    children,
    ...restProps
  }: WithElementRef<HTMLThAttributes> = $props();
</script>

<th
  bind:this={ref}
  data-slot="table-head"
  class={cn(
    'text-foreground h-10 whitespace-nowrap bg-clip-padding px-2 text-left align-middle font-medium [&:has([role=checkbox])]:pr-0',
    className,
  )}
  {...restProps}
>
  {@render children?.()}
</th>
