<script lang="ts">
  import { ScrollArea as ScrollAreaPrimitive } from 'bits-ui';

  import { type WithoutChild, cn } from '@slink/utils/ui/index.js';

  import { Scrollbar } from './index.js';
  import {
    type ScrollAreaViewportVariants,
    scrollAreaViewportTheme,
  } from './scroll-area.theme';

  let {
    ref = $bindable(null),
    class: className,
    orientation = 'vertical',
    maxHeight,
    children,
    ...restProps
  }: WithoutChild<ScrollAreaPrimitive.RootProps> &
    ScrollAreaViewportVariants & {
      orientation?: 'vertical' | 'horizontal' | 'both' | undefined;
    } = $props();
</script>

<ScrollAreaPrimitive.Root
  bind:ref
  data-slot="scroll-area"
  class={cn('relative', className)}
  {...restProps}
>
  <ScrollAreaPrimitive.Viewport
    data-slot="scroll-area-viewport"
    class={scrollAreaViewportTheme({ maxHeight })}
  >
    {@render children?.()}
  </ScrollAreaPrimitive.Viewport>
  {#if orientation === 'vertical' || orientation === 'both'}
    <Scrollbar orientation="vertical" />
  {/if}
  {#if orientation === 'horizontal' || orientation === 'both'}
    <Scrollbar orientation="horizontal" />
  {/if}
  <ScrollAreaPrimitive.Corner />
</ScrollAreaPrimitive.Root>
