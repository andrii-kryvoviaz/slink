<script lang="ts">
  import CheckIcon from '@lucide/svelte/icons/check';
  import ChevronDownIcon from '@lucide/svelte/icons/chevron-down';
  import LoaderIcon from '@lucide/svelte/icons/loader';
  import XIcon from '@lucide/svelte/icons/x';
  import { Combobox } from 'bits-ui';
  import type { Snippet } from 'svelte';

  import { cn } from '@slink/utils/ui/index.js';

  type ComboboxItem = {
    value: string;
    label: string;
    disabled?: boolean;
  };

  interface Props {
    items: ComboboxItem[];
    value?: string;
    placeholder?: string;
    onValueChange?: (value: string) => void;
    onSearch?: (query: string) => void;
    loading?: boolean;
    class?: string;
    disabled?: boolean;
    error?: string;
    emptyMessage?: string;
    clearable?: boolean;
    itemRenderer?: Snippet<[{ item: ComboboxItem; selected: boolean }]>;
    size?: 'sm' | 'default';
  }

  let {
    items,
    value = $bindable(),
    placeholder = 'Search...',
    onValueChange,
    onSearch,
    loading = false,
    class: className,
    disabled = false,
    error,
    emptyMessage = 'No items found.',
    clearable = true,
    itemRenderer,
    size = 'default',
  }: Props = $props();

  let searchValue = $state('');

  const filteredItems = $derived(
    onSearch
      ? items
      : searchValue === ''
        ? items
        : items.filter((item) =>
            item.label.toLowerCase().includes(searchValue.toLowerCase()),
          ),
  );

  const handleSearchInput = (query: string) => {
    searchValue = query;

    if (onSearch) {
      onSearch(query);
    }
  };

  const selectedItem = $derived(
    value ? items.find((item) => item.value === value) : null,
  );

  const handleSelect = (itemValue: string) => {
    value = itemValue;
    onValueChange?.(itemValue);
  };

  const handleClear = (e: Event) => {
    e.stopPropagation();
    value = '';
    searchValue = '';
    onValueChange?.('');
  };

  const handleOpenChange = (open: boolean) => {
    if (!open) {
      searchValue = selectedItem?.label || '';
    }
  };
</script>

<div class={cn('relative', className)}>
  <Combobox.Root
    type="single"
    {value}
    onValueChange={(newValue: string | undefined) => {
      if (newValue) {
        handleSelect(newValue);
      }
    }}
    onOpenChangeComplete={handleOpenChange}
    {disabled}
  >
    <div class="relative">
      <Combobox.Input
        oninput={(e) => handleSearchInput(e.currentTarget.value)}
        {placeholder}
        data-size={size}
        class={cn(
          "border-border data-[placeholder]:text-muted-foreground [&_svg:not([class*='text-'])]:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive dark:bg-input/30 dark:hover:bg-input/50 shadow-xs flex w-full select-none items-center justify-between gap-2 whitespace-nowrap rounded-md border bg-transparent px-4 py-2.5 pr-12 text-sm outline-none transition-[color,box-shadow] focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 data-[size=default]:h-11 data-[size=sm]:h-9",
        )}
      />
      <div
        class="absolute right-4 top-1/2 -translate-y-1/2 flex items-center gap-1"
      >
        {#if selectedItem && clearable}
          <button
            type="button"
            onclick={handleClear}
            class="p-0.5 hover:bg-muted rounded transition-colors cursor-pointer"
            aria-label="Clear selection"
          >
            <XIcon class="h-3 w-3 opacity-50" />
          </button>
        {/if}
        <Combobox.Trigger class="flex items-center">
          {#if loading}
            <LoaderIcon class="size-4 opacity-50 animate-spin" />
          {:else}
            <ChevronDownIcon class="size-4 opacity-50" />
          {/if}
        </Combobox.Trigger>
      </div>
    </div>
    <Combobox.Portal>
      <Combobox.Content
        sideOffset={4}
        class={cn(
          'bg-white dark:bg-gray-900/95 text-gray-900 dark:text-gray-100 backdrop-blur-sm border border-gray-200/80 dark:border-gray-700/80 data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2 max-h-[var(--bits-combobox-content-available-height)] origin-[var(--bits-combobox-content-transform-origin)] relative z-50 min-w-[8rem] w-[var(--bits-combobox-anchor-width)] overflow-y-auto overflow-x-hidden rounded-xl shadow-xl shadow-black/10 dark:shadow-black/25 data-[side=bottom]:translate-y-1 data-[side=left]:-translate-x-1 data-[side=right]:translate-x-1 data-[side=top]:-translate-y-1',
        )}
      >
        <Combobox.Viewport class="p-1">
          {#each filteredItems as item (item.value)}
            <Combobox.Item
              value={item.value}
              label={item.label}
              disabled={item.disabled}
              class={cn(
                'data-highlighted:bg-blue-100 dark:data-highlighted:bg-blue-800/40 data-highlighted:text-blue-600 dark:data-highlighted:text-blue-300 outline-hidden relative flex w-full cursor-default select-none items-center gap-2 rounded-lg py-2.5 pl-3 pr-3 text-sm font-medium text-gray-700 dark:text-gray-200 transition-all duration-150 hover:bg-blue-100 dark:hover:bg-blue-800/40 hover:text-blue-600 dark:hover:text-blue-300 data-[disabled]:pointer-events-none data-[disabled]:opacity-50',
              )}
            >
              {#snippet children({ selected })}
                <CheckIcon
                  class={cn(
                    'mr-2 size-4 text-blue-600 dark:text-blue-400',
                    !selected && 'opacity-0',
                  )}
                />
                {#if itemRenderer}
                  {@render itemRenderer({ item, selected })}
                {:else}
                  {item.label}
                {/if}
              {/snippet}
            </Combobox.Item>
          {:else}
            <div class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">
              {emptyMessage}
            </div>
          {/each}
        </Combobox.Viewport>
      </Combobox.Content>
    </Combobox.Portal>
  </Combobox.Root>

  {#if error}
    <p class="text-sm text-red-600 dark:text-red-400 mt-1">
      {error}
    </p>
  {/if}
</div>
