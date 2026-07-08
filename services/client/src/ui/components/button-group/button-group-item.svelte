<script lang="ts">
  import { Tooltip } from '@slink/ui/components/tooltip';
  import type { Snippet } from 'svelte';

  import type { HTMLButtonAttributes } from 'svelte/elements';

  import { type WithElementRef, cn } from '@slink/utils/ui';

  import {
    type ButtonGroupItemVariant,
    type ButtonGroupSize,
    buttonGroupItemVariants,
  } from './button-group.svelte';

  type Props = WithElementRef<HTMLButtonAttributes> & {
    variant?: ButtonGroupItemVariant;
    size?: ButtonGroupSize;
    active?: boolean;
    tooltip?: string;
    disableTooltip?: boolean;
    children?: Snippet;
  };

  let {
    class: customClass,
    variant = 'default',
    size = 'md',
    active = false,
    tooltip,
    disableTooltip,
    ref = $bindable(null),
    children,
    ...restProps
  }: Props = $props();
</script>

{#if tooltip && !disableTooltip}
  <Tooltip
    side="bottom"
    sideOffset={8}
    withArrow={false}
    triggerProps={{ class: 'flex' }}
  >
    {#snippet trigger()}
      <button
        bind:this={ref}
        type="button"
        class={cn(
          buttonGroupItemVariants({ variant, size, active }),
          customClass,
        )}
        {...restProps}
      >
        {@render children?.()}
      </button>
    {/snippet}
    {tooltip}
  </Tooltip>
{:else}
  <button
    bind:this={ref}
    type="button"
    class={cn(buttonGroupItemVariants({ variant, size, active }), customClass)}
    {...restProps}
  >
    {@render children?.()}
  </button>
{/if}
