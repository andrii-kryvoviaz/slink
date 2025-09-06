<script lang="ts">
  import type { HTMLAttributes } from 'svelte/elements';

  import { type WithElementRef, cn } from '@slink/utils/ui/index.js';

  let {
    ref = $bindable(null),
    class: className,
    children,
    ...restProps
  }: WithElementRef<HTMLAttributes<HTMLTableRowElement>> = $props();
</script>

<tr
  bind:this={ref}
  data-slot="table-row"
  class={cn(
    'hover:[&,&>svelte-css-wrapper]:[&>th,td]:bg-muted/50 data-[state=selected]:bg-muted border-b transition-colors',
    className,
  )}
  {...restProps}
>
  {@render children?.()}
</tr>
