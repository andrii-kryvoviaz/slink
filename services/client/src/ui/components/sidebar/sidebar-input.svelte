<script lang="ts">
  import { Input } from '@slink/ui/components/input/index.js';
  import type { ComponentProps } from 'svelte';

  import { cn } from '@slink/utils/ui/index.js';

  let {
    ref = $bindable(null),
    value = $bindable(''),
    class: className,
    ...restProps
  }: ComponentProps<typeof Input> = $props();
</script>

<Input
  bind:ref
  bind:value
  data-slot="sidebar-input"
  data-sidebar="input"
  class={cn('bg-background h-8 w-full shadow-none', className)}
  {...restProps}
/>
