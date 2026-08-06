<script lang="ts">
  import { DropdownMenu as DropdownMenuPrimitive } from 'bits-ui';

  import { cn } from '@slink/utils/ui/index.js';

  let {
    ref = $bindable(null),
    class: className,
    ...restProps
  }: DropdownMenuPrimitive.SubContentProps = $props();
</script>

<DropdownMenuPrimitive.SubContent
  bind:ref
  data-slot="dropdown-menu-sub-content"
  class={cn(
    'popover-panel bg-popover text-popover-foreground origin-(--bits-dropdown-menu-content-transform-origin) min-w-[8rem] overflow-hidden p-1',
    className,
  )}
  {...restProps}
/>
