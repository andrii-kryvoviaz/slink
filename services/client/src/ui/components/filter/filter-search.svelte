<script lang="ts">
  import { Command as CommandPrimitive, Popover } from 'bits-ui';
  import type { Snippet } from 'svelte';

  import { debounce } from '$lib/utils/time/debounce';

  import { cn } from '@slink/utils/ui/index.js';

  import {
    FilterSearchState,
    setFilterContainerContext,
    setFilterSearchContext,
  } from './context.svelte';
  import FilterContainer from './filter-container.svelte';
  import FilterField from './filter-field.svelte';
  import {
    type FilterRounded,
    type FilterSize,
    type FilterVariant,
    filterContentVariants,
  } from './filter.theme';

  type KeyboardHandler = (event: KeyboardEvent) => void;

  interface Props {
    searchTerm?: string;
    open?: boolean;
    disabled?: boolean;
    placeholder?: string;
    hideIcon?: boolean;
    variant?: FilterVariant;
    size?: FilterSize;
    rounded?: FilterRounded;
    autocomplete?: boolean;
    shouldFilter?: boolean;
    wrap?: boolean;
    debounceMs?: number;
    class?: string;
    contentClass?: string;
    shellClass?: string;
    inputClass?: string;
    onSearch?: (searchTerm: string) => void;
    onClear?: () => void;
    onEnter?: KeyboardHandler;
    onEscape?: KeyboardHandler;
    onArrowDown?: KeyboardHandler;
    onArrowUp?: KeyboardHandler;
    onBackspaceEmpty?: KeyboardHandler;
    leading?: Snippet;
    trailing?: Snippet;
    content?: Snippet;
    field?: Snippet;
  }

  let {
    searchTerm = $bindable(''),
    open = $bindable(false),
    disabled = false,
    placeholder = 'Search...',
    hideIcon = false,
    variant = 'default',
    size = 'md',
    rounded = 'lg',
    autocomplete = false,
    shouldFilter = false,
    wrap = false,
    debounceMs = 0,
    class: className,
    contentClass,
    shellClass,
    inputClass,
    onSearch,
    onClear,
    onEnter,
    onEscape,
    onArrowDown,
    onArrowUp,
    onBackspaceEmpty,
    leading,
    trailing,
    content,
    field,
  }: Props = $props();

  setFilterContainerContext({
    variant,
    size,
    disabled: () => disabled,
  });

  const state = setFilterSearchContext(
    new FilterSearchState({
      autocomplete,
      getSearchTerm: () => searchTerm,
      setSearchTerm: (v) => (searchTerm = v),
      getOpen: () => open,
      setOpen: (v) => (open = v),
      onEnter: () => onEnter,
      onEscape: () => onEscape,
      onArrowDown: () => onArrowDown,
      onArrowUp: () => onArrowUp,
      onBackspaceEmpty: () => onBackspaceEmpty,
    }),
  );

  export const focus = () => state.focus();
  export const blur = () => state.blur();
  export const clear = () => state.clear();

  const debouncedSearch = $derived(
    debounceMs > 0
      ? debounce((value: string) => onSearch?.(value), debounceMs)
      : null,
  );

  let previousTerm = '';
  $effect(() => {
    const term = searchTerm;
    if (term === previousTerm) return;
    previousTerm = term;

    if (term.trim()) {
      if (debouncedSearch) debouncedSearch(term.trim());
      else onSearch?.(term.trim());
    } else {
      debouncedSearch?.cancel();
      onClear?.();
    }
  });

  $effect(() => {
    if (disabled) state.open = false;
  });

  const handleContainerClick = (event: MouseEvent) => {
    if (disabled) return;
    const target = event.target as HTMLElement;
    if (target.closest('button, [role="button"], input')) return;
    state.focus();
  };

  const handleContainerKeydown = (event: KeyboardEvent) => {
    if (disabled) return;
    if (event.key !== 'Enter' && event.key !== ' ') return;
    const target = event.target as HTMLElement;
    if (target.tagName === 'INPUT') return;
    event.preventDefault();
    state.focus();
  };

  const closeAndBlur = () => {
    state.open = false;
    state.blur();
  };
</script>

{#snippet shellContent(extraProps: Record<string, unknown> = {})}
  <FilterContainer
    {...extraProps}
    {variant}
    {size}
    {rounded}
    {disabled}
    {wrap}
    open={state.open}
    role="combobox"
    aria-expanded={autocomplete ? !disabled && state.open : undefined}
    aria-disabled={disabled}
    tabindex={disabled ? -1 : 0}
    onclick={handleContainerClick}
    onkeydown={handleContainerKeydown}
    class={cn(shellClass, className)}
  >
    {@render leading?.()}
    {#if field}
      {@render field()}
    {:else}
      <FilterField {placeholder} {hideIcon} class={inputClass} />
    {/if}
    {@render trailing?.()}
  </FilterContainer>
{/snippet}

{#if autocomplete}
  <CommandPrimitive.Root {shouldFilter} loop class="contents">
    <Popover.Root bind:open={state.open}>
      <Popover.Trigger disabled={true}>
        {#snippet child({ props })}
          {@render shellContent(props)}
        {/snippet}
      </Popover.Trigger>
      {#if content}
        <Popover.Content
          class={cn(filterContentVariants({ variant }), contentClass)}
          sideOffset={4}
          avoidCollisions={true}
          collisionPadding={10}
          trapFocus={false}
          interactOutsideBehavior="close"
          onOpenAutoFocus={(e) => e.preventDefault()}
          onCloseAutoFocus={(e) => e.preventDefault()}
          onInteractOutside={closeAndBlur}
          onEscapeKeydown={closeAndBlur}
        >
          {@render content()}
        </Popover.Content>
      {/if}
    </Popover.Root>
  </CommandPrimitive.Root>
{:else}
  {@render shellContent()}
{/if}
