<script lang="ts">
  import Icon from '@iconify/svelte';

  import { className } from '@slink/utils/ui/className';

  import {
    ViewModeToggleButton,
    ViewModeToggleContainer,
    ViewModeToggleIcon,
  } from './ViewModeToggle.theme';
  import type {
    ViewModeOption,
    ViewModeToggleProps,
  } from './ViewModeToggle.types';

  interface Props extends ViewModeToggleProps {}

  let {
    value,
    options = [
      {
        value: 'grid',
        label: 'Grid',
        icon: 'heroicons:squares-2x2',
      },
      {
        value: 'list',
        label: 'List',
        icon: 'heroicons:bars-3',
      },
    ],
    size = 'md',
    className: customClassName = '',
    onValueChange,
  }: Props = $props();

  const handleValueChange = (newValue: ViewModeOption['value']) => {
    onValueChange(newValue);
  };
</script>

<div class={className(ViewModeToggleContainer({ size }), customClassName)}>
  {#each options as option (option.value)}
    <button
      type="button"
      onclick={() => handleValueChange(option.value)}
      class={ViewModeToggleButton({
        variant: value === option.value ? 'active' : 'inactive',
        size,
      })}
      aria-pressed={value === option.value}
      aria-label={`Switch to ${option.label} view`}
    >
      <Icon icon={option.icon} class={ViewModeToggleIcon({ size })} />
      {option.label}
    </button>
  {/each}
</div>
