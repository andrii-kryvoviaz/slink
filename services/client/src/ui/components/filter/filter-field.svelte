<script lang="ts">
  import { Command as CommandPrimitive } from 'bits-ui';

  import Icon from '@iconify/svelte';

  import { cn } from '@slink/utils/ui/index.js';

  import {
    getFilterContainerContext,
    getFilterSearchContext,
  } from './context.svelte';
  import {
    filterClearButtonVariants,
    filterFieldVariants,
    filterLeadingIconVariants,
  } from './filter.theme';

  interface Props {
    placeholder?: string;
    hideIcon?: boolean;
    leadingIcon?: string;
    value?: string;
    onValueChange?: (value: string) => void;
    onClear?: () => void;
    class?: string;
    inputClass?: string;
  }

  let {
    placeholder = 'Search...',
    hideIcon = false,
    leadingIcon = 'ph:magnifying-glass',
    value = $bindable(),
    onValueChange,
    onClear,
    class: className,
    inputClass,
  }: Props = $props();

  const container = getFilterContainerContext();
  const search = getFilterSearchContext();

  let inputEl = $state<HTMLInputElement | null>(null);

  $effect(() => {
    if (search) {
      search.registerInput(inputEl ?? undefined);
      return () => search.registerInput(undefined);
    }
  });

  const currentValue = $derived(search ? search.searchTerm : (value ?? ''));
  const disabled = $derived(container.disabled());

  const handleFocus = () => {
    if (search?.autocomplete && !disabled) {
      search.open = true;
    }
  };

  const handleKeydown = (e: KeyboardEvent) => {
    if (disabled) return;
    search?.handleKeydown(e);
  };

  const handleInput = (e: Event) => {
    const next = (e.currentTarget as HTMLInputElement).value;
    if (search) {
      search.searchTerm = next;
    } else {
      value = next;
      onValueChange?.(next);
    }
  };

  const handleClear = () => {
    if (search) {
      search.clear();
    } else {
      value = '';
      onValueChange?.('');
    }
    onClear?.();
  };

  const inputClassName = $derived(
    cn(
      filterFieldVariants({ variant: container.variant, size: container.size }),
      inputClass,
    ),
  );
</script>

<div class={cn('flex items-center gap-2 flex-1 min-w-0', className)}>
  {#if !hideIcon}
    <Icon
      icon={leadingIcon}
      class={filterLeadingIconVariants({
        variant: container.variant,
        size: container.size,
      })}
    />
  {/if}

  {#if search?.autocomplete}
    <CommandPrimitive.Input
      bind:ref={inputEl}
      value={currentValue}
      oninput={handleInput}
      onkeydown={handleKeydown}
      onfocus={handleFocus}
      {placeholder}
      {disabled}
      class={inputClassName}
      autocomplete="off"
      spellcheck="false"
    />
  {:else}
    <input
      bind:this={inputEl}
      value={currentValue}
      oninput={handleInput}
      onkeydown={handleKeydown}
      onfocus={handleFocus}
      {placeholder}
      {disabled}
      class={inputClassName}
      type="text"
      autocomplete="off"
      spellcheck="false"
    />
  {/if}

  {#if currentValue.trim() && !disabled}
    <button
      type="button"
      onclick={handleClear}
      aria-label="Clear"
      class={filterClearButtonVariants({
        variant: container.variant,
        size: container.size,
      })}
    >
      <Icon icon="ph:x" class="w-3 h-3" />
    </button>
  {/if}
</div>
