<script lang="ts">
  import { Popover as PopoverPrimitive } from 'bits-ui';

  let { ref = $bindable(null), ...restProps }: PopoverPrimitive.TriggerProps =
    $props();
</script>

<PopoverPrimitive.Trigger
  bind:ref
  data-slot="action-popover-trigger"
  {...restProps}
/>
