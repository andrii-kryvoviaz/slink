<script lang="ts">
  import { ContextMenu as ContextMenuPrimitive } from 'bits-ui';

  import { cn } from '@slink/utils/ui/index.js';

  import { contextMenuItemTheme } from './context-menu-item.theme';

  let {
    ref = $bindable(null),
    class: className,
    inset,
    variant = 'default',
    ...restProps
  }: ContextMenuPrimitive.ItemProps & {
    inset?: boolean;
    variant?: 'default' | 'destructive';
  } = $props();
</script>

<ContextMenuPrimitive.Item
  bind:ref
  data-slot="context-menu-item"
  data-inset={inset}
  data-variant={variant}
  class={cn(
    contextMenuItemTheme({ variant, inset: Boolean(inset) }),
    className,
  )}
  {...restProps}
/>
