<script lang="ts">
  import { Tooltip as TooltipPrimitive } from 'bits-ui';

  import { cn } from '@slink/utils/ui/index.js';

  import { tooltipArrowVariants, tooltipVariants } from './tooltip.theme.js';
  import type {
    TooltipRounded,
    TooltipSize,
    TooltipVariant,
    TooltipWidth,
  } from './tooltip.theme.js';

  let {
    ref = $bindable(null),
    class: className,
    sideOffset = 4,
    side = 'top',
    children,
    arrowClasses,
    variant = 'default',
    size = 'sm',
    rounded = 'md',
    width = 'auto',
    withArrow = true,
    ...restProps
  }: TooltipPrimitive.ContentProps & {
    arrowClasses?: string;
    variant?: TooltipVariant;
    size?: TooltipSize;
    rounded?: TooltipRounded;
    width?: TooltipWidth;
    withArrow?: boolean;
  } = $props();
</script>

<TooltipPrimitive.Portal>
  <TooltipPrimitive.Content
    bind:ref
    data-slot="tooltip-content"
    {sideOffset}
    {side}
    class={cn(tooltipVariants({ variant, size, rounded, width }), className)}
    {...restProps}
  >
    {@render children?.()}
    {#if withArrow}
      <TooltipPrimitive.Arrow>
        {#snippet child({ props })}
          <div
            class={cn(
              tooltipArrowVariants({ variant, rounded }),
              'data-[side=top]:translate-x-1/2 data-[side=top]:translate-y-[calc(-50%_+_2px)]',
              'data-[side=bottom]:-translate-x-1/2 data-[side=bottom]:-translate-y-[calc(-50%_+_1px)]',
              'data-[side=right]:translate-x-[calc(50%_+_2px)] data-[side=right]:translate-y-1/2',
              'data-[side=left]:-translate-y-[calc(50%_-_3px)]',
              arrowClasses,
            )}
            {...props}
          ></div>
        {/snippet}
      </TooltipPrimitive.Arrow>
    {/if}
  </TooltipPrimitive.Content>
</TooltipPrimitive.Portal>
