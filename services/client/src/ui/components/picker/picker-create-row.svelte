<script lang="ts">
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  import {
    type PickerColor,
    type PickerVariant,
    pickerCreateRowTheme,
  } from './picker.theme';

  interface Props {
    term: string;
    color?: PickerColor;
    variant?: PickerVariant;
    isCreating?: boolean;
    onclick: () => void;
    label?: Snippet<[string]>;
  }

  let {
    term,
    color = 'blue',
    variant = 'popover',
    isCreating = false,
    onclick,
    label,
  }: Props = $props();
</script>

{#snippet defaultLabel(value: string)}
  Create <span class="font-semibold">"{value}"</span>
{/snippet}

<button
  type="button"
  class={pickerCreateRowTheme({ variant, color })}
  {onclick}
  disabled={isCreating}
  aria-busy={isCreating}
>
  {#if isCreating}
    <Icon icon="ph:circle-notch" class="w-3.5 h-3.5 shrink-0 animate-spin" />
  {:else}
    <Icon icon="ph:plus" class="w-3.5 h-3.5 shrink-0" />
  {/if}

  <span class="flex-1 truncate text-[13px]">
    {@render (label ?? defaultLabel)(term)}
  </span>

  <kbd
    class="shrink-0 rounded-sm border border-current px-1.5 py-0.5 text-[10px] font-medium uppercase tracking-wide opacity-50"
  >
    Enter
  </kbd>
</button>
