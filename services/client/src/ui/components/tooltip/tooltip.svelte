<script lang="ts">
  import type { Snippet } from 'svelte';

  import { browser } from '$app/environment';

  import * as TooltipPrimitive from './index.js';
  import type {
    TooltipRounded,
    TooltipSize,
    TooltipVariant,
    TooltipWidth,
  } from './tooltip.theme.js';

  interface Props {
    trigger: Snippet;
    children?: Snippet;
    open?: boolean;
    side?: 'top' | 'right' | 'bottom' | 'left';
    sideOffset?: number;
    align?: 'start' | 'center' | 'end';
    alignOffset?: number;
    withArrow?: boolean;
    variant?: TooltipVariant;
    size?: TooltipSize;
    rounded?: TooltipRounded;
    width?: TooltipWidth;
    class?: string;
    triggerProps?: Record<string, unknown>;
    [key: string]: unknown;
  }

  let {
    trigger,
    children,
    open = $bindable(false),
    side = 'top',
    sideOffset = 4,
    align = 'center',
    alignOffset = 0,
    withArrow = true,
    variant = 'subtle',
    size = 'sm',
    rounded = 'md',
    width = 'auto',
    class: className,
    triggerProps = {},
    ...restProps
  }: Props = $props();
</script>

{#if !browser}
  {@render trigger()}
{:else}
  <TooltipPrimitive.Root bind:open>
    <TooltipPrimitive.Trigger {...triggerProps}>
      {@render trigger()}
    </TooltipPrimitive.Trigger>
    <TooltipPrimitive.Content
      {side}
      {sideOffset}
      {align}
      {alignOffset}
      {variant}
      {size}
      {rounded}
      {width}
      {withArrow}
      class={className}
      {...restProps}
    >
      {@render children?.()}
    </TooltipPrimitive.Content>
  </TooltipPrimitive.Root>
{/if}
