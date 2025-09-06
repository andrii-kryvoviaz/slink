<script lang="ts">
  import { twMerge } from 'tailwind-merge';

  import { Theme } from '$lib/settings';
  import Icon from '@iconify/svelte';
  import type { HTMLButtonAttributes } from 'svelte/elements';

  import {
    ThemeSwitchContainer,
    ThemeSwitchIcon,
    ThemeSwitchTheme,
    ThemeSwitchTooltip,
  } from './ThemeSwitch.theme';
  import type { ThemeSwitchProps } from './ThemeSwitch.types';

  interface Props extends Omit<HTMLButtonAttributes, 'size'>, ThemeSwitchProps {
    disabled?: boolean;
    checked?: boolean;
    showTooltip?: boolean;
    tooltipText?: string;
    customIcons?: {
      light?: string;
      dark?: string;
    };
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
    customIcons,
    class: customClass = '',
    on,
    ...buttonProps
  }: Props = $props();

  const buttonClasses = $derived(
    twMerge(ThemeSwitchTheme({ variant, size, animation }), customClass),
  );

  const iconClasses = $derived(
    ThemeSwitchIcon({
      variant,
      size,
      animation:
        animation === 'subtle'
          ? 'scale'
          : animation === 'bounce'
            ? 'bounce'
            : animation === 'smooth'
              ? 'scale'
              : 'none',
    }),
  );

  const containerClasses = $derived(
    ThemeSwitchContainer({ tooltip: showTooltip }),
  );

  const defaultTooltip = $derived(
    tooltipText || (checked ? 'Switch to light mode' : 'Switch to dark mode'),
  );

  const lightIcon = $derived(customIcons?.light || 'ph:sun-thin');
  const darkIcon = $derived(customIcons?.dark || 'ph:moon-thin');

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
      <Icon icon={darkIcon} class={iconClasses} />
    {:else}
      <Icon icon={lightIcon} class={iconClasses} />
    {/if}
  </button>

  {#if showTooltip}
    <div class={ThemeSwitchTooltip()}>
      {defaultTooltip}
    </div>
  {/if}
</div>
