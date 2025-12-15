<script lang="ts">
  import { LinkPreview as HoverCardPrimitive } from 'bits-ui';

  import { cn } from '@slink/utils/ui/index.js';

  import {
    type HoverCardRounded,
    type HoverCardSize,
    type HoverCardVariant,
    type HoverCardWidth,
    hoverCardVariants,
  } from './hover-card.theme';

  let {
    ref = $bindable(null),
    class: className,
    align = 'center',
    sideOffset = 4,
    variant = 'default',
    size = 'md',
    rounded = 'md',
    width = 'sm',
    portalProps,
    ...restProps
  }: HoverCardPrimitive.ContentProps & {
    portalProps?: HoverCardPrimitive.PortalProps;
    variant?: HoverCardVariant;
    size?: HoverCardSize;
    rounded?: HoverCardRounded;
    width?: HoverCardWidth;
  } = $props();
</script>

<HoverCardPrimitive.Portal {...portalProps}>
  <HoverCardPrimitive.Content
    bind:ref
    data-slot="hover-card-content"
    {align}
    {sideOffset}
    class={cn(hoverCardVariants({ variant, size, rounded, width }), className)}
    {...restProps}
  />
</HoverCardPrimitive.Portal>
