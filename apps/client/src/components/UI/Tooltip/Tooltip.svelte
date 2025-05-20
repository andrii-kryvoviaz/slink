<script lang="ts">
  import type { TooltipProps } from '@slink/components/UI/Tooltip/Tooltip.types';
  import { Tooltip } from 'bits-ui';
  import { type Snippet } from 'svelte';

  import { className } from '@slink/utils/ui/className';

  import {
    TooltipArrow,
    TooltipContent,
  } from '@slink/components/UI/Tooltip/Tooltip.theme';

  type Props = Tooltip.RootProps &
    Tooltip.ContentProps &
    TooltipProps & {
      trigger: Snippet;
      triggerProps?: Tooltip.TriggerProps;
      withArrow?: boolean;
    };

  let {
    open = $bindable(false),
    trigger,
    children,
    triggerProps = {},
    withArrow = false,
    variant = 'default',
    size = 'xs',
    width = 'auto',
    rounded = 'md',
    ...props
  }: Props = $props();

  let contentClassess = $derived(
    className(
      `${TooltipContent({ variant, size, width, rounded })} ${props.class}`,
    ),
  );
  let arrowClassess = $derived(className(TooltipArrow({ variant })));
</script>

<Tooltip.Root bind:open>
  <Tooltip.Trigger {...triggerProps}>
    {@render trigger()}
  </Tooltip.Trigger>
  <Tooltip.Portal>
    <Tooltip.Content sideOffset={8} {...props} class={contentClassess}>
      {#if withArrow}
        <Tooltip.Arrow class={arrowClassess} />
      {/if}
      {@render children?.()}
    </Tooltip.Content>
  </Tooltip.Portal>
</Tooltip.Root>
