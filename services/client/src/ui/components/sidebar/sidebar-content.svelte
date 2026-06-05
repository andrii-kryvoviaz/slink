<script lang="ts">
  import { ScrollArea } from '@slink/ui/components/scroll-area/index.js';

  import type { HTMLAttributes } from 'svelte/elements';

  import { type WithElementRef, cn } from '@slink/utils/ui/index.js';

  let {
    ref = $bindable(null),
    class: className,
    children,
    ...restProps
  }: WithElementRef<HTMLAttributes<HTMLElement>> = $props();
</script>

<div
  bind:this={ref}
  data-slot="sidebar-content"
  data-sidebar="content"
  class={cn(
    'flex min-h-0 flex-1 flex-col group-data-[collapsible=icon]:overflow-hidden',
    className,
  )}
  {...restProps}
>
  <ScrollArea class="flex-1 min-h-0" orientation="vertical" type="scroll">
    <div class="flex flex-col gap-2">
      {@render children?.()}
    </div>
  </ScrollArea>
</div>
