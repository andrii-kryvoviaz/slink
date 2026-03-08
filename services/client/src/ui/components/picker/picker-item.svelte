<script lang="ts">
  import { Loader } from '@slink/feature/Layout';
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  import type { SelectionState } from '@slink/lib/state';

  import {
    type PickerColor,
    type PickerVariant,
    pickerCheckIconTheme,
    pickerCheckboxTheme,
    pickerItemTheme,
    pickerNameTheme,
    pickerSubtextTheme,
  } from './picker.theme';

  interface Props {
    children: Snippet;
    subtext?: Snippet;
    selected?: boolean | SelectionState;
    highlighted?: boolean;
    isToggling?: boolean;
    disabled?: boolean;
    variant?: PickerVariant;
    color?: PickerColor;
    onclick?: () => void;
  }

  let {
    children,
    subtext,
    selected = false,
    highlighted = false,
    isToggling = false,
    disabled = false,
    variant = 'popover',
    color = 'blue',
    onclick,
  }: Props = $props();

  const resolvedState: SelectionState = $derived.by(() => {
    if (typeof selected === 'boolean') {
      return selected ? 'checked' : 'unchecked';
    }

    return selected ?? 'unchecked';
  });
  const isChecked = $derived(resolvedState === 'checked');
  const isPartial = $derived(resolvedState === 'partial');
  const isSelected = $derived(resolvedState !== 'unchecked');

  const handleClick = () => {
    if (!disabled && onclick) {
      onclick();
    }
  };
</script>

<button
  type="button"
  class={pickerItemTheme({ variant, color, selected: isSelected, highlighted })}
  onclick={handleClick}
  data-highlighted={highlighted || undefined}
  {disabled}
>
  <span class={pickerCheckboxTheme({ variant, color, selected: isSelected })}>
    {#if isToggling}
      <Loader variant="minimal" size="xs" />
    {:else if isChecked}
      <Icon icon="ph:check-bold" class={pickerCheckIconTheme({ variant })} />
    {:else if isPartial}
      <Icon icon="ph:minus-bold" class={pickerCheckIconTheme({ variant })} />
    {/if}
  </span>
  <span class="flex-1 min-w-0 flex flex-col">
    <span class={pickerNameTheme({ variant, color, selected: isSelected })}>
      {@render children()}
    </span>
    {#if subtext}
      <span
        class={pickerSubtextTheme({ variant, color, selected: isSelected })}
      >
        {@render subtext()}
      </span>
    {/if}
  </span>
</button>
