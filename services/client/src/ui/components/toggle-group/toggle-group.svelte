<script lang="ts" generics="T extends string | number = string">
  import Icon from '@iconify/svelte';

  import { className } from '@slink/utils/ui/className';

  import {
    toggleGroupIconTheme,
    toggleGroupInnerTheme,
    toggleGroupItemTheme,
    toggleGroupTheme,
  } from './toggle-group.theme';
  import type { ToggleGroupProps } from './toggle-group.types';

  interface Props extends ToggleGroupProps<T> {}

  let {
    value,
    options,
    onValueChange,
    size = 'md',
    rounded = 'lg',
    orientation = 'horizontal',
    className: customClassName = '',
    'aria-label': ariaLabel,
    disabled = false,
  }: Props = $props();

  const innerRoundedMap: Record<
    string,
    'none' | 'sm' | 'md' | 'lg' | 'xl' | 'full'
  > = {
    none: 'none',
    sm: 'none',
    md: 'sm',
    lg: 'md',
    xl: 'lg',
    full: 'full',
  };

  const innerRounded = $derived(innerRoundedMap[rounded]);

  const handleValueChange = (newValue: T) => {
    if (!disabled) {
      onValueChange(newValue);
    }
  };
</script>

<div
  class={className(
    toggleGroupTheme({ size, orientation, rounded }),
    customClassName,
  )}
  role="radiogroup"
  aria-label={ariaLabel}
  aria-orientation={orientation}
>
  <div class={toggleGroupInnerTheme({ orientation, rounded: innerRounded })}>
    {#each options as option (option.value)}
      {@const isActive = value === option.value}
      {@const isDisabled = disabled || option.disabled}

      <button
        type="button"
        role="radio"
        aria-checked={isActive}
        aria-label={option.label
          ? `Select ${option.label}`
          : `Select ${option.value}`}
        disabled={isDisabled}
        onclick={() => handleValueChange(option.value)}
        class={toggleGroupItemTheme({
          variant: isActive ? 'active' : 'inactive',
          size,
          orientation,
        })}
      >
        {#if option.icon}
          <Icon
            icon={option.icon}
            class={toggleGroupIconTheme({
              size,
              hasLabel: !!option.label,
            })}
          />
        {/if}
        {#if option.label}
          <span>{option.label}</span>
        {/if}
      </button>
    {/each}
  </div>
</div>
