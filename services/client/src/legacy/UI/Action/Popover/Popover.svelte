<script lang="ts">
  import {
    PopoverArrowTheme,
    PopoverContentTheme,
    PopoverTriggerTheme,
  } from '@slink/legacy/UI/Action/Popover/Popover.theme';
  import type { PopoverProps } from '@slink/legacy/UI/Action/Popover/Popover.types';
  import { Popover } from 'bits-ui';
  import type { Snippet } from 'svelte';

  import { browser } from '$app/environment';

  import { responsive } from '@slink/lib/stores/responsive.svelte';
  import { className } from '@slink/lib/utils/ui/className';

  type Props = Popover.RootProps &
    Popover.ContentProps &
    PopoverProps & {
      trigger?: Snippet;
      triggerProps?: Popover.TriggerProps;
      contentProps?: Popover.ContentProps;
      withArrow?: boolean;
      responsive?: boolean;
      class?: string;
      contentClass?: string;
      triggerClass?: string;
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
  <Popover.Root bind:open {...props}>
    <Popover.Trigger {...triggerProps} class={triggerClasses}>
      {@render trigger?.()}
    </Popover.Trigger>

    <Popover.Portal>
      <Popover.Content
        side="bottom"
        align="start"
        sideOffset={8}
        {...responsiveContentProps()}
        class={contentClasses}
      >
        {#if withArrow}
          <Popover.Arrow class={arrowClasses} />
        {/if}
        {@render children?.()}
      </Popover.Content>
    </Popover.Portal>
  </Popover.Root>
{/if}
