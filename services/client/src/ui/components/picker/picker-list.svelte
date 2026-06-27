<script lang="ts" generics="T">
  import { Loader } from '@slink/feature/Layout';
  import { ScrollArea } from '@slink/ui/components/scroll-area/index.js';
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  import PickerCreateFooter from './picker-create-footer.svelte';
  import PickerCreateRow from './picker-create-row.svelte';
  import PickerEmptyState from './picker-empty-state.svelte';
  import PickerSearch from './picker-search.svelte';
  import {
    type PickerColor,
    type PickerVariant,
    pickerContainerTheme,
    pickerListTheme,
  } from './picker.theme';
  import type { PickerCreate } from './picker.types';

  interface Props {
    items: T[];
    variant?: PickerVariant;
    color?: PickerColor;
    isLoading?: boolean;
    searchPlaceholder?: string;
    showSearch?: boolean;
    children: Snippet<[{ item: T; index: number; highlighted: boolean }]>;
    emptyIcon?: Snippet;
    emptyMessage?: Snippet;
    emptyDescription?: Snippet;
    createFooter?: Snippet;
    filterFn?: (item: T, searchTerm: string) => boolean;
    create?: PickerCreate;
  }

  let {
    items,
    variant = 'popover',
    color = 'blue',
    isLoading = false,
    searchPlaceholder = 'Search...',
    showSearch: showSearchProp,
    children,
    emptyIcon,
    emptyMessage,
    emptyDescription,
    createFooter,
    filterFn,
    create,
  }: Props = $props();

  let searchTerm = $state('');
  let isCreating = $state(false);

  const hasInstant = $derived(!!create?.instant);

  const showSearch = $derived(
    showSearchProp !== undefined
      ? showSearchProp
      : items.length > 5 || hasInstant,
  );

  const filteredItems = $derived(
    searchTerm && filterFn
      ? items.filter((item) => filterFn(item, searchTerm))
      : items,
  );

  let highlightedIndex = $state(-1);

  $effect(() => {
    searchTerm;
    highlightedIndex = -1;
  });

  async function runCreate() {
    const name = searchTerm.trim();
    if (!name || isCreating || !create?.instant) return;

    isCreating = true;

    try {
      await create.instant(name);
      searchTerm = '';
    } catch {
      isCreating = false;
      return;
    }

    isCreating = false;
  }

  const handleKeydown = (e: KeyboardEvent) => {
    const canCreate = hasInstant && !!searchTerm.trim() && !isCreating;

    if (e.key === 'Enter' && highlightedIndex < 0 && canCreate) {
      e.preventDefault();
      runCreate();
      return;
    }

    if (filteredItems.length === 0) return;

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      highlightedIndex =
        highlightedIndex < filteredItems.length - 1 ? highlightedIndex + 1 : 0;
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      highlightedIndex =
        highlightedIndex > 0 ? highlightedIndex - 1 : filteredItems.length - 1;
    } else if (e.key === 'Enter' && highlightedIndex >= 0) {
      e.preventDefault();
    }
  };
</script>

{#snippet defaultCreateLabel()}Create{/snippet}

<div
  class={pickerContainerTheme({ variant })}
  onkeydown={handleKeydown}
  role="listbox"
  tabindex="0"
>
  {#if (variant === 'popover' || variant === 'glass') && showSearch}
    <PickerSearch bind:value={searchTerm} placeholder={searchPlaceholder} />
  {/if}

  {#if isLoading}
    <div class="flex items-center justify-center py-10">
      <Loader variant="minimal" size="sm" />
    </div>
  {:else if items.length === 0}
    {#if create?.instant && searchTerm.trim()}
      <div class={pickerListTheme({ variant })}>
        <PickerCreateRow
          term={searchTerm.trim()}
          {color}
          {variant}
          {isCreating}
          onclick={runCreate}
        />
      </div>
    {:else}
      <PickerEmptyState {color}>
        {#snippet icon()}
          {#if emptyIcon}
            {@render emptyIcon()}
          {:else}
            <Icon icon="ph:list-dashes" class="w-5 h-5" />
          {/if}
        {/snippet}
        {#snippet message()}
          {#if emptyMessage}
            {@render emptyMessage()}
          {:else}
            No items yet
          {/if}
        {/snippet}
        {#snippet description()}
          {#if emptyDescription}
            {@render emptyDescription()}
          {/if}
        {/snippet}
      </PickerEmptyState>
    {/if}

    {#if create?.detailed && (!hasInstant || !searchTerm.trim())}
      <PickerCreateFooter onclick={() => create?.detailed?.(searchTerm)}>
        {@render (createFooter ?? defaultCreateLabel)()}
      </PickerCreateFooter>
    {/if}
  {:else}
    <ScrollArea maxHeight="sm" orientation="vertical" type="scroll">
      <div class={pickerListTheme({ variant })}>
        {#if filteredItems.length === 0}
          {#if create?.instant && searchTerm.trim()}
            <PickerCreateRow
              term={searchTerm.trim()}
              {color}
              {variant}
              {isCreating}
              onclick={runCreate}
            />
          {:else}
            <div class="flex flex-col items-center gap-2 py-4">
              <div
                class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center"
              >
                <Icon
                  icon="ph:magnifying-glass"
                  class="w-5 h-5 text-gray-400 dark:text-gray-500"
                />
              </div>
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                No matches found
              </p>
            </div>
          {/if}
        {:else}
          {#each filteredItems as item, index (index)}
            {@render children({
              item,
              index,
              highlighted: index === highlightedIndex,
            })}
          {/each}
          {#if create?.instant && searchTerm.trim()}
            <PickerCreateRow
              term={searchTerm.trim()}
              {color}
              {variant}
              {isCreating}
              onclick={runCreate}
            />
          {/if}
        {/if}
      </div>
    </ScrollArea>

    {#if create?.detailed && (!hasInstant || !searchTerm.trim())}
      <PickerCreateFooter onclick={() => create?.detailed?.(searchTerm)}>
        {@render (createFooter ?? defaultCreateLabel)()}
      </PickerCreateFooter>
    {/if}
  {/if}
</div>
