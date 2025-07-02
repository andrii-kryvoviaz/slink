<script lang="ts">
  import { ScrollArea } from 'bits-ui';
  import type { WithoutChild } from 'bits-ui';

  import { className as cn } from '@slink/utils/ui/className';

  import {
    ScrollAreaCornerTheme,
    ScrollAreaScrollbarTheme,
    ScrollAreaTheme,
    ScrollAreaThumbTheme,
    ScrollAreaViewportTheme,
  } from './ScrollArea.theme';

  interface Props extends WithoutChild<ScrollArea.RootProps> {
    variant?: 'default' | 'minimal' | 'modern' | 'glass';
    size?: 'sm' | 'md' | 'lg' | 'xl' | 'full' | 'auto';
    orientation?: 'vertical' | 'horizontal' | 'both';
    viewportClass?: string;
    viewportPadding?: 'none' | 'sm' | 'md' | 'lg';
    scrollbarClass?: string;
    thumbClass?: string;
    cornerClass?: string;
    scrollbarSize?: 'sm' | 'md' | 'lg';
    hideDelay?: number;
    class?: string;
  }

  let {
    ref = $bindable(null),
    variant = 'minimal',
    size = 'auto',
    orientation = 'vertical',
    viewportClass = '',
    viewportPadding = 'none',
    scrollbarClass = '',
    thumbClass = '',
    cornerClass = '',
    scrollbarSize = 'md',
    hideDelay = 600,
    type = 'scroll',
    children,
    class: className = '',
    ...restProps
  }: Props = $props();
</script>

{#snippet Scrollbar({
  orientation,
}: {
  orientation: 'vertical' | 'horizontal';
})}
  <ScrollArea.Scrollbar
    {orientation}
    class={cn(
      ScrollAreaScrollbarTheme({
        variant,
        orientation,
        size: scrollbarSize,
      }),
      scrollbarClass,
    )}
  >
    <ScrollArea.Thumb
      class={cn(ScrollAreaThumbTheme({ variant }), thumbClass)}
    />
  </ScrollArea.Scrollbar>
{/snippet}

<ScrollArea.Root
  bind:ref
  {type}
  scrollHideDelay={hideDelay}
  class={cn(ScrollAreaTheme({ variant, size }), className)}
  {...restProps}
>
  <ScrollArea.Viewport
    class={cn(
      ScrollAreaViewportTheme({ variant, padding: viewportPadding }),
      viewportClass,
    )}
  >
    {@render children?.()}
  </ScrollArea.Viewport>

  {#if orientation === 'vertical' || orientation === 'both'}
    {@render Scrollbar({ orientation: 'vertical' })}
  {/if}

  {#if orientation === 'horizontal' || orientation === 'both'}
    {@render Scrollbar({ orientation: 'horizontal' })}
  {/if}

  <ScrollArea.Corner
    class={cn(ScrollAreaCornerTheme({ variant }), cornerClass)}
  />
</ScrollArea.Root>
