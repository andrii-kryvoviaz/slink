<script lang="ts">
  import ChevronDownIcon from '@lucide/svelte/icons/chevron-down';
  import * as Command from '@slink/ui/components/command';
  import * as Popover from '@slink/ui/components/popover';
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

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
    searchPlaceholder?: string;
    onValueChange?: (value: string) => void;
    class?: string;
    disabled?: boolean;
    error?: string;
    emptyMessage?: string;
    clearable?: boolean;
    itemRenderer?: Snippet<[{ item: ComboboxItem; selected: boolean }]>;
  }

  let {
    items,
    value = $bindable(),
    placeholder = 'Select...',
    searchPlaceholder = 'Search...',
    onValueChange,
    class: className,
    disabled = false,
    error,
    emptyMessage = 'No items found.',
    clearable = true,
    itemRenderer,
  }: Props = $props();

  let isOpen = $state(false);
  let triggerElement: HTMLElement | undefined = $state();
  let popoverWidth = $state<number | undefined>(undefined);

  const selectedItem = $derived(
    value ? items.find((item) => item.value === value) : null,
  );

  const selectedValue = $derived(selectedItem?.label);

  const handleSelect = (itemValue: string) => {
    value = itemValue;
    onValueChange?.(itemValue);
    isOpen = false;
  };

  const handleClear = (e: Event) => {
    e.stopPropagation();
    value = '';
    onValueChange?.('');
  };

  const handleOpenChange = (open: boolean) => {
    isOpen = open;
    if (open && triggerElement) {
      popoverWidth = triggerElement.offsetWidth;
    }
  };
</script>

<div class={cn('relative', className)}>
  <Popover.Root open={isOpen} onOpenChange={handleOpenChange}>
    <Popover.Trigger>
      {#snippet child({ props })}
        {@const { class: inheritClass, ...triggerProps } = props}
        <button
          bind:this={triggerElement}
          {...triggerProps}
          type="button"
          role="combobox"
          aria-expanded={isOpen}
          data-placeholder={selectedValue ? undefined : ''}
          data-size="default"
          class={cn(
            "border-border data-[placeholder]:text-muted-foreground [&_svg:not([class*='text-'])]:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive dark:bg-input/30 dark:hover:bg-input/50 shadow-xs flex w-full select-none items-center justify-between gap-2 whitespace-nowrap rounded-md border bg-transparent px-3 py-2 text-sm outline-none transition-[color,box-shadow] focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 data-[size=default]:h-9 data-[size=sm]:h-8 [&_svg:not([class*='size-'])]:size-4 [&_svg]:pointer-events-none [&_svg]:shrink-0",
            inheritClass as string,
          )}
          {disabled}
        >
          <div class="flex items-center gap-2 min-w-0 flex-1">
            <span class="truncate">
              {selectedValue || placeholder}
            </span>
          </div>
          <div class="flex items-center gap-1 shrink-0">
            {#if selectedItem && clearable}
              <div
                role="button"
                tabindex="0"
                onclick={handleClear}
                onkeydown={(e: KeyboardEvent) => {
                  if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    handleClear(e as any);
                  }
                }}
                class="p-0.5 hover:bg-muted rounded transition-colors cursor-pointer"
                aria-label="Clear selection"
              >
                <Icon icon="lucide:x" class="h-3 w-3" />
              </div>
            {/if}
            <ChevronDownIcon class="size-4 opacity-50" />
          </div>
        </button>
      {/snippet}
    </Popover.Trigger>
    <Popover.Content
      sideOffset={4}
      style={popoverWidth ? `width: ${popoverWidth}px` : undefined}
      class={cn(
        'p-0 bg-white dark:bg-gray-900/95 text-gray-900 dark:text-gray-100 backdrop-blur-sm border border-gray-200/80 dark:border-gray-700/80 data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2 max-h-(--bits-select-content-available-height) origin-(--bits-select-content-transform-origin) relative z-50 overflow-y-auto overflow-x-hidden rounded-xl shadow-xl shadow-black/10 dark:shadow-black/25 data-[side=bottom]:translate-y-1 data-[side=left]:-translate-x-1 data-[side=right]:translate-x-1 data-[side=top]:-translate-y-1',
      )}
    >
      <Command.Root class="bg-transparent">
        <Command.Input
          placeholder={searchPlaceholder}
          class="text-gray-900 dark:text-gray-100 placeholder:text-gray-500 dark:placeholder:text-gray-400 [&>div]:border-gray-200/80 [&>div]:dark:border-gray-700/80"
        />
        <Command.List class="scroll-my-1 p-1">
          <Command.Empty class="text-gray-500 dark:text-gray-400"
            >{emptyMessage}</Command.Empty
          >
          <Command.Group>
            {#each items as item (item.value)}
              {@const isSelected = value === item.value}
              <Command.Item
                value={item.value}
                onSelect={() => handleSelect(item.value)}
                disabled={item.disabled}
                class="text-gray-700 dark:text-gray-200 font-medium py-2.5 pl-3 pr-8 rounded-lg transition-all duration-150 hover:bg-blue-100 dark:hover:bg-blue-800/40 hover:text-blue-600 dark:hover:text-blue-300 aria-selected:bg-blue-100 dark:aria-selected:bg-blue-800/40 aria-selected:text-blue-600 dark:aria-selected:text-blue-300 data-[disabled]:text-gray-400 dark:data-[disabled]:text-gray-500"
              >
                {#if itemRenderer}
                  {@render itemRenderer({ item, selected: isSelected })}
                {:else}
                  <Icon
                    icon="lucide:check"
                    class={cn(
                      'absolute right-2 h-4 w-4 text-blue-600 dark:text-blue-400',
                      !isSelected && 'text-transparent',
                    )}
                  />
                  {item.label}
                {/if}
              </Command.Item>
            {/each}
          </Command.Group>
        </Command.List>
      </Command.Root>
    </Popover.Content>
  </Popover.Root>

  {#if error}
    <p class="text-sm text-red-600 dark:text-red-400 mt-1">
      {error}
    </p>
  {/if}
</div>
