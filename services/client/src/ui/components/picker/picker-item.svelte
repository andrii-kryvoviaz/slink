<script lang="ts">
  import { Loader } from '@slink/feature/Layout';
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

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
    selected?: boolean;
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
    isToggling = false,
    disabled = false,
    variant = 'popover',
    color = 'blue',
    onclick,
  }: Props = $props();

  const handleClick = () => {
    if (!disabled && onclick) {
      onclick();
    }
  };
</script>

<button
  type="button"
  class={pickerItemTheme({ variant, color, selected })}
  onclick={handleClick}
  {disabled}
>
  <span class={pickerCheckboxTheme({ variant, color, selected })}>
    {#if isToggling}
      <Loader variant="minimal" size="xs" />
    {:else if selected}
      <Icon icon="ph:check-bold" class={pickerCheckIconTheme({ variant })} />
    {/if}
  </span>
  <span class="flex-1 min-w-0 flex flex-col">
    <span class={pickerNameTheme({ variant, color, selected })}>
      {@render children()}
    </span>
    {#if subtext}
      <span class={pickerSubtextTheme({ variant, color, selected })}>
        {@render subtext()}
      </span>
    {/if}
  </span>
</button>
