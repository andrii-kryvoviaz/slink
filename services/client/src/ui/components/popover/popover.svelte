<script lang="ts">
  import { Popover as PopoverPrimitive } from 'bits-ui';
  import type { Snippet } from 'svelte';

  import { browser } from '$app/environment';

  import { responsive } from '@slink/lib/stores/responsive.svelte';
  import { className } from '@slink/lib/utils/ui/className';

  import PopoverContent from './popover-content.svelte';
  import PopoverTrigger from './popover-trigger.svelte';
  import {
    PopoverArrowTheme,
    PopoverContentTheme,
    PopoverTriggerTheme,
  } from './themes';

  type Props = {
    open?: boolean;
    trigger?: Snippet;
    triggerProps?: Record<string, any>;
    contentProps?: Record<string, any>;
    withArrow?: boolean;
    responsive?: boolean;
    class?: string;
    contentClass?: string;
    triggerClass?: string;
    children?: Snippet;
    variant?: 'default' | 'glass' | 'floating' | 'minimal' | 'modern';
    triggerVariant?: 'default' | 'button' | 'ghost' | 'minimal';
    size?: 'none' | 'xs' | 'sm' | 'md' | 'lg' | 'xl' | 'auto';
    rounded?: 'none' | 'sm' | 'md' | 'lg' | 'xl' | '2xl' | 'full';
  };

  let {
    open = $bindable(false),
    trigger,
    children,
    triggerProps = {},
    contentProps = {},
    withArrow = false,
    responsive: enableResponsive = false,
    variant = 'default',
    triggerVariant = 'default',
    size = 'md',
    rounded = 'lg',
    class: customClass,
    contentClass,
    triggerClass,
    ...props
  }: Props = $props();

  let responsiveContentProps = $derived(() => {
    if (!enableResponsive) return contentProps;

    const responsiveProps = {
      side: responsive.isMobile
        ? ('top' as const)
        : (contentProps.side ?? ('bottom' as const)),
      align: responsive.isMobile
        ? ('center' as const)
        : (contentProps.align ?? ('start' as const)),
      sideOffset: responsive.isMobile ? 12 : (contentProps.sideOffset ?? 8),
      alignOffset: responsive.isMobile ? 0 : (contentProps.alignOffset ?? 0),
      avoidCollisions: true,
      collisionPadding: responsive.isMobile
        ? { top: 20, right: 20, bottom: 20, left: 20 }
        : (contentProps.collisionPadding ?? 16),
      sticky: 'partial' as const,
    };

    return { ...contentProps, ...responsiveProps };
  });

  let contentClasses = $derived(
    className(
      PopoverContentTheme({ variant, size, rounded }),
      contentClass,
      customClass,
    ),
  );

  let arrowClasses = $derived(className(PopoverArrowTheme({ variant })));

  let triggerClasses = $derived(
    className(PopoverTriggerTheme({ variant: triggerVariant }), triggerClass),
  );
</script>

{#if !browser}
  {@render trigger?.()}
{:else}
  <PopoverPrimitive.Root bind:open {...props}>
    <PopoverTrigger {...triggerProps} class={triggerClasses}>
      {@render trigger?.()}
    </PopoverTrigger>

    <PopoverContent
      side="bottom"
      align="start"
      sideOffset={8}
      {...responsiveContentProps()}
      class={contentClasses}
    >
      {#if withArrow}
        <div class={arrowClasses}></div>
      {/if}
      {@render children?.()}
    </PopoverContent>
  </PopoverPrimitive.Root>
{/if}
