<script lang="ts">
  import { Popover as PopoverPrimitive } from 'bits-ui';

  import { cn } from '@slink/utils/ui/index.js';

  let {
    ref = $bindable(null),
    class: className,
    ...restProps
  }: PopoverPrimitive.TriggerProps = $props();
</script>

<PopoverPrimitive.Trigger
  bind:ref
  data-slot="popover-trigger"
  class={cn('', className)}
  {...restProps}
/>
