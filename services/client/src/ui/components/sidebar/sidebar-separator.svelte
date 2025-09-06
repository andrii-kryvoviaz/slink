<script lang="ts">
  import { Separator } from '@slink/ui/components/separator/index.js';
  import type { ComponentProps } from 'svelte';

  import { cn } from '@slink/utils/ui/index.js';

  let {
    ref = $bindable(null),
    class: className,
    ...restProps
  }: ComponentProps<typeof Separator> = $props();
</script>

<Separator
  bind:ref
  data-slot="sidebar-separator"
  data-sidebar="separator"
  class={cn('bg-sidebar-border', className)}
  {...restProps}
/>
