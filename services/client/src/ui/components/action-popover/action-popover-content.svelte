<script lang="ts">
  import XIcon from '@lucide/svelte/icons/x';
  import { Popover as PopoverPrimitive } from 'bits-ui';

  import { cn } from '@slink/utils/ui/index.js';

  import {
    ActionPopoverActionsTheme,
    ActionPopoverArrowTheme,
    ActionPopoverBodyTheme,
    ActionPopoverCloseTheme,
    ActionPopoverContentTheme,
    ActionPopoverDescriptionTheme,
    ActionPopoverFooterTheme,
    ActionPopoverHeaderTheme,
    ActionPopoverIconBoxTheme,
    ActionPopoverTitleBlockTheme,
    ActionPopoverTitleTheme,
  } from './action-popover.theme';
  import type { ActionPopoverContentProps } from './types';

  let {
    tone = 'default',
    size = 'md',
    width,
    side = 'bottom',
    align = 'start',
    sideOffset = 8,
    alignOffset = 0,
    withArrow = false,
    closable = false,
    onClose,
    class: className,
    icon,
    title,
    description,
    actions,
    children,
    footer,
  }: ActionPopoverContentProps = $props();

  const hasHeader = $derived(
    Boolean(icon) ||
      Boolean(title) ||
      Boolean(description) ||
      Boolean(actions) ||
      closable,
  );

  function handleClose(): void {
    onClose?.();
  }
</script>

<PopoverPrimitive.Portal>
  <PopoverPrimitive.Content
    data-slot="action-popover-content"
    {side}
    {align}
    {sideOffset}
    {alignOffset}
    class={cn(ActionPopoverContentTheme({ tone, size }), width, className)}
  >
    {#if withArrow}
      <div class={ActionPopoverArrowTheme()}></div>
    {/if}

    {#if hasHeader}
      <div class={ActionPopoverHeaderTheme()}>
        <div class="flex min-w-0 items-start gap-3">
          {#if icon}
            <div class={ActionPopoverIconBoxTheme({ tone })}>
              {@render icon()}
            </div>
          {/if}

          {#if title || description}
            <div class={ActionPopoverTitleBlockTheme()}>
              {#if title}
                <h3 class={ActionPopoverTitleTheme()}>
                  {@render title()}
                </h3>
              {/if}
              {#if description}
                <p class={ActionPopoverDescriptionTheme()}>
                  {@render description()}
                </p>
              {/if}
            </div>
          {/if}
        </div>

        {#if actions || closable}
          <div class={ActionPopoverActionsTheme()}>
            {#if actions}
              {@render actions()}
            {/if}
            {#if closable}
              <button
                type="button"
                class={ActionPopoverCloseTheme()}
                aria-label="Close"
                onclick={handleClose}
              >
                <XIcon class="h-4 w-4" />
              </button>
            {/if}
          </div>
        {/if}
      </div>
    {/if}

    {#if children}
      <div class={ActionPopoverBodyTheme({ hasHeader })}>
        {@render children()}
      </div>
    {/if}

    {#if footer}
      <div class={ActionPopoverFooterTheme()}>
        {@render footer()}
      </div>
    {/if}
  </PopoverPrimitive.Content>
</PopoverPrimitive.Portal>
