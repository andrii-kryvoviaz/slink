<script lang="ts">
  import type { HTMLTdAttributes } from 'svelte/elements';

  import { type WithElementRef, cn } from '@slink/utils/ui/index.js';

  let {
    ref = $bindable(null),
    class: className,
    children,
    ...restProps
  }: WithElementRef<HTMLTdAttributes> = $props();
</script>

<td
  bind:this={ref}
  data-slot="table-cell"
  class={cn(
    'whitespace-nowrap bg-clip-padding p-2 align-middle [&:has([role=checkbox])]:pr-0',
    className,
  )}
  {...restProps}
>
  {@render children?.()}
</td>
