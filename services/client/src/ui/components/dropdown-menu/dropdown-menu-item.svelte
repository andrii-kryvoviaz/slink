<script lang="ts">
  import { DropdownMenu as DropdownMenuPrimitive } from 'bits-ui';

  import { cn } from '@slink/utils/ui/index.js';

  import { dropdownMenuItemTheme } from './dropdown-menu-item.theme';

  let {
    ref = $bindable(null),
    class: className,
    inset,
    variant = 'default',
    ...restProps
  }: DropdownMenuPrimitive.ItemProps & {
    inset?: boolean;
    variant?: 'default' | 'destructive';
  } = $props();
</script>

<DropdownMenuPrimitive.Item
  bind:ref
  data-slot="dropdown-menu-item"
  data-inset={inset}
  data-variant={variant}
  class={cn(
    dropdownMenuItemTheme({ variant, inset: Boolean(inset) }),
    className,
  )}
  {...restProps}
/>
