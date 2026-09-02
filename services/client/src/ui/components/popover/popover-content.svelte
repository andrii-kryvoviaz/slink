<script lang="ts">
  import { Popover as PopoverPrimitive } from 'bits-ui';

  import { cn } from '@slink/utils/ui/index.js';

  let {
    ref = $bindable(null),
    class: className,
    sideOffset = 4,
    align = 'center',
    portalProps,
    ...restProps
  }: PopoverPrimitive.ContentProps & {
    portalProps?: PopoverPrimitive.PortalProps;
  } = $props();
</script>

<PopoverPrimitive.Portal {...portalProps}>
  <PopoverPrimitive.Content
    bind:ref
    data-slot="popover-content"
    {sideOffset}
    {align}
    class={cn(
      'popover-panel bg-card dark:bg-card/95 text-foreground origin-(--bits-popover-content-transform-origin) outline-hidden',
      className,
    )}
    {...restProps}
  />
</PopoverPrimitive.Portal>
