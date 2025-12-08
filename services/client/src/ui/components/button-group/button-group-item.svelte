<script lang="ts">
  import { Tooltip } from '@slink/ui/components/tooltip';
  import type { Snippet } from 'svelte';

  import type { HTMLButtonAttributes } from 'svelte/elements';

  import { type WithElementRef, cn } from '@slink/utils/ui';

  import {
    type ButtonGroupItemPosition,
    type ButtonGroupItemVariant,
    type ButtonGroupSize,
    buttonGroupItemVariants,
  } from './button-group.svelte';

  type Props = WithElementRef<HTMLButtonAttributes> & {
    variant?: ButtonGroupItemVariant;
    size?: ButtonGroupSize;
    position?: ButtonGroupItemPosition;
    active?: boolean;
    tooltip?: string;
    children?: Snippet;
  };

  let {
    class: customClass,
    variant = 'default',
    size = 'md',
    position = 'middle',
    active = false,
    tooltip,
    ref = $bindable(null),
    children,
    ...restProps
  }: Props = $props();
</script>

{#if tooltip}
  <Tooltip
    side="bottom"
    sideOffset={8}
    withArrow={false}
    triggerProps={{ class: 'flex flex-1' }}
  >
    {#snippet trigger()}
      <button
        bind:this={ref}
        type="button"
        class={cn(
          buttonGroupItemVariants({ variant, size, position, active }),
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
    class={cn(
      buttonGroupItemVariants({ variant, size, position, active }),
      customClass,
    )}
    {...restProps}
  >
    {@render children?.()}
  </button>
{/if}
