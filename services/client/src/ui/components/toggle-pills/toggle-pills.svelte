<script lang="ts" generics="T extends string = string">
  import { ToggleGroup as ToggleGroupPrimitive } from 'bits-ui';

  import Icon from '@iconify/svelte';

  import { className } from '@slink/utils/ui/className';

  import {
    togglePillsIconTheme,
    togglePillsItemTheme,
    togglePillsTheme,
  } from './toggle-pills.theme';
  import type { TogglePillsProps } from './toggle-pills.types';

  interface Props extends TogglePillsProps<T> {}

  let {
    options,
    value = $bindable([]),
    onValueChange,
    minItems = 0,
    size = 'md',
    disabled = false,
    className: customClassName = '',
    'aria-label': ariaLabel,
    item,
  }: Props = $props();

  const atMinimum = $derived(value.length <= minItems);
  const lockedValues = $derived(new Set<T>(atMinimum ? value : []));

  const handleValueChange = (next: string[]) => {
    value = next as T[];
    onValueChange?.(value);
  };
</script>

<ToggleGroupPrimitive.Root
  type="multiple"
  {disabled}
  bind:value={() => value, handleValueChange}
  aria-label={ariaLabel}
  class={className(togglePillsTheme(), customClassName)}
>
  {#each options as option (option.value)}
    <ToggleGroupPrimitive.Item
      value={option.value}
      disabled={option.disabled || lockedValues.has(option.value)}
      class={togglePillsItemTheme({ size })}
    >
      {#snippet children({ pressed })}
        {#if item}
          {@render item({ option, pressed })}
        {:else}
          {#if pressed}
            <Icon icon="lucide:check" class={togglePillsIconTheme({ size })} />
          {:else}
            <Icon icon="lucide:plus" class={togglePillsIconTheme({ size })} />
          {/if}
          <span>{option.label ?? option.value}</span>
        {/if}
      {/snippet}
    </ToggleGroupPrimitive.Item>
  {/each}
</ToggleGroupPrimitive.Root>
