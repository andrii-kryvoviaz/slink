<script lang="ts">
  import {
    ThemeSwitchContainer,
    ThemeSwitchIcon,
    ThemeSwitchTheme,
    ThemeSwitchTooltip,
  } from '@slink/feature/Layout/ThemeSwitch/ThemeSwitch.theme';
  import type {
    ThemeSwitchAnimation,
    ThemeSwitchProps,
  } from '@slink/feature/Layout/ThemeSwitch/ThemeSwitch.types';
  import { twMerge } from 'tailwind-merge';

  import { Theme } from '$lib/settings';
  import Icon from '@iconify/svelte';
  import type { HTMLButtonAttributes } from 'svelte/elements';

  interface Props extends Omit<HTMLButtonAttributes, 'size'>, ThemeSwitchProps {
    disabled?: boolean;
    checked?: boolean;
    showTooltip?: boolean;
    tooltipText?: string;
    class?: string;
    on: { change: (theme: Theme) => void };
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
    twMerge(ThemeSwitchTheme({ variant, size, animation }), customClass),
  );

  const animationMap: Record<
    NonNullable<ThemeSwitchAnimation>,
    'scale' | 'bounce' | 'none'
  > = {
    subtle: 'scale',
    bounce: 'bounce',
    smooth: 'scale',
    none: 'none',
  };

  const iconClasses = $derived(
    ThemeSwitchIcon({
      variant,
      size,
      animation: animation ? animationMap[animation] : 'none',
    }),
  );

  const containerClasses = $derived(
    ThemeSwitchContainer({ tooltip: showTooltip }),
  );

  const defaultTooltip = $derived(
    tooltipText || (checked ? 'Switch to light mode' : 'Switch to dark mode'),
  );

  const handleThemeChange = () => {
    if (disabled) return;
    on.change(checked ? Theme.LIGHT : Theme.DARK);
  };
</script>

<div class={containerClasses}>
  <button
    type="button"
    class={buttonClasses}
    onclick={handleThemeChange}
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
    <div class={ThemeSwitchTooltip()}>
      {defaultTooltip}
    </div>
  {/if}
</div>
