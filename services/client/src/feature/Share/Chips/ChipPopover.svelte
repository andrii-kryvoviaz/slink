<script lang="ts">
  import {
    AttributeChip,
    type AttributeChipState,
  } from '@slink/ui/components/attribute-chip';
  import {
    PopoverContent,
    Root as PopoverRoot,
    PopoverTrigger,
  } from '@slink/ui/components/popover';
  import type { Snippet } from 'svelte';

  import { browser } from '$app/environment';

  import { cn } from '@slink/utils/ui/index.js';

  import {
    ChipPopoverSurface,
    type ChipPopoverVariant,
  } from './ChipPopover.theme';

  interface Props {
    open?: boolean;
    state?: AttributeChipState;
    label: string;
    removeLabel?: string;
    disabled?: boolean;
    onRemove?: () => void;
    leading?: Snippet;
    variant?: ChipPopoverVariant;
    contentClass?: string;
    children: Snippet;
  }

  let {
    open = $bindable(false),
    state = 'ghost',
    label,
    removeLabel,
    disabled = false,
    onRemove,
    leading,
    variant = 'dark',
    contentClass = 'w-max min-w-72 max-w-[22rem] p-2',
    children,
  }: Props = $props();
</script>

{#if !browser}
  <AttributeChip
    {state}
    {label}
    {removeLabel}
    {disabled}
    {onRemove}
    {leading}
  />
{:else}
  <PopoverRoot bind:open>
    <PopoverTrigger>
      {#snippet child({ props })}
        <AttributeChip
          {state}
          {label}
          {removeLabel}
          {disabled}
          {onRemove}
          {leading}
          {...props}
        />
      {/snippet}
    </PopoverTrigger>
    <PopoverContent
      side="bottom"
      align="start"
      sideOffset={8}
      class={cn(ChipPopoverSurface({ variant }), contentClass)}
    >
      {@render children()}
    </PopoverContent>
  </PopoverRoot>
{/if}
