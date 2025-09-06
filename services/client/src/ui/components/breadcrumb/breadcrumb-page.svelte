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
  data-slot="breadcrumb-page"
  role="link"
  aria-disabled="true"
  aria-current="page"
  class={cn('text-foreground font-normal', className)}
  {...restProps}
>
  {@render children?.()}
</span>
