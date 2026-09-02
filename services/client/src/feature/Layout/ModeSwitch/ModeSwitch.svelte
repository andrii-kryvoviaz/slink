<script lang="ts">
  import {
    ModeSwitchContainer,
    ModeSwitchIcon,
    ModeSwitchTheme,
    ModeSwitchTooltip,
  } from '@slink/feature/Layout/ModeSwitch/ModeSwitch.theme';
  import type {
    ModeSwitchAnimation,
    ModeSwitchProps,
  } from '@slink/feature/Layout/ModeSwitch/ModeSwitch.types';
  import { twMerge } from 'tailwind-merge';

  import { Mode } from '$lib/settings';
  import Icon from '@iconify/svelte';
  import type { HTMLButtonAttributes } from 'svelte/elements';

  interface Props extends Omit<HTMLButtonAttributes, 'size'>, ModeSwitchProps {
    disabled?: boolean;
    checked?: boolean;
    showTooltip?: boolean;
    tooltipText?: string;
    class?: string;
    on: { change: (mode: Mode) => void };
  }

  let {
    disabled = false,
    checked = false,
    variant = 'default',
    size = 'md',
    animation = 'subtle',
    showTooltip = false,
    tooltipText,
    class: customClass = '',
    on,
    ...buttonProps
  }: Props = $props();

  const buttonClasses = $derived(
    twMerge(ModeSwitchTheme({ variant, size, animation }), customClass),
  );

  const animationMap: Record<
    NonNullable<ModeSwitchAnimation>,
    'scale' | 'bounce' | 'none'
  > = {
    subtle: 'scale',
    bounce: 'bounce',
    smooth: 'scale',
    none: 'none',
  };

  const iconClasses = $derived(
    ModeSwitchIcon({
      variant,
      size,
      animation: animation ? animationMap[animation] : 'none',
    }),
  );

  const containerClasses = $derived(
    ModeSwitchContainer({ tooltip: showTooltip }),
  );

  const defaultTooltip = $derived(
    tooltipText || (checked ? 'Switch to light mode' : 'Switch to dark mode'),
  );

  const handleModeChange = () => {
    if (disabled) return;
    on.change(checked ? Mode.LIGHT : Mode.DARK);
  };
</script>

<div class={containerClasses}>
  <button
    type="button"
    class={buttonClasses}
    onclick={handleModeChange}
    {disabled}
    aria-label={defaultTooltip}
    {...buttonProps}
  >
    {#if checked}
      <Icon icon="ph:moon-thin" class={iconClasses} />
    {:else}
      <Icon icon="ph:sun-thin" class={iconClasses} />
    {/if}
  </button>

  {#if showTooltip}
    <div class={ModeSwitchTooltip()}>
      {defaultTooltip}
    </div>
  {/if}
</div>
