<script lang="ts">
  import type { Snippet } from 'svelte';

  import { cn } from '$lib/utils/ui';
  import Icon from '@iconify/svelte';
  import type { HTMLButtonAttributes } from 'svelte/elements';

  import {
    type AttributeChipState,
    attributeChip,
  } from './attribute-chip.theme';

  interface Props extends HTMLButtonAttributes {
    state?: AttributeChipState;
    label: string;
    removeLabel?: string;
    disabled?: boolean;
    leading?: Snippet;
    onRemove?: () => void;
    class?: string;
  }

  let {
    state = 'ghost',
    label,
    removeLabel,
    disabled = false,
    leading,
    onRemove,
    class: className,
    ...rest
  }: Props = $props();

  const theme = $derived(attributeChip({ state, disabled }));
</script>

{#if state === 'set'}
  <span class={cn(theme.root(), className)}>
    <button type="button" class={theme.body()} {disabled} {...rest}>
      {#if leading}
        {@render leading()}
      {/if}
      <span class={theme.label()}>{label}</span>
    </button>
    {#if onRemove}
      <button
        type="button"
        class={theme.remove()}
        aria-label={removeLabel ?? label}
        {disabled}
        onclick={onRemove}
      >
        <Icon icon="ph:x" class={theme.removeIcon()} />
      </button>
    {/if}
  </span>
{:else}
  <button
    type="button"
    class={cn(theme.root(), theme.body(), className)}
    {disabled}
    {...rest}
  >
    <Icon icon="ph:plus-bold" class={theme.plusIcon()} />
    <span class={theme.label()}>{label}</span>
  </button>
{/if}
