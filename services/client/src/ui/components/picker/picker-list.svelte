<script lang="ts" generics="T">
  import { Loader } from '@slink/feature/Layout';
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  import PickerCreateFooter from './picker-create-footer.svelte';
  import PickerEmptyState from './picker-empty-state.svelte';
  import PickerSearch from './picker-search.svelte';
  import {
    type PickerColor,
    type PickerVariant,
    pickerContainerTheme,
    pickerListTheme,
  } from './picker.theme';

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
    emptyAction?: Snippet;
    createFooter?: Snippet;
    filterFn?: (item: T, searchTerm: string) => boolean;
    onCreateNew?: () => void;
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
    emptyAction,
    createFooter,
    filterFn,
    onCreateNew,
  }: Props = $props();

  let searchTerm = $state('');

  const showSearch = $derived(
    showSearchProp !== undefined ? showSearchProp : items.length > 5,
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

  const handleKeydown = (e: KeyboardEvent) => {
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
    <PickerEmptyState {color} onAction={onCreateNew}>
      {#snippet icon()}
        {#if emptyIcon}
          {@render emptyIcon()}
        {:else}
          <Icon
            icon="ph:list-dashes"
            class="w-5 h-5 text-gray-400 dark:text-gray-500"
          />
        {/if}
      {/snippet}
      {#snippet message()}
        {#if emptyMessage}
          {@render emptyMessage()}
        {:else}
          No items yet
        {/if}
      {/snippet}
      {#snippet action()}
        {#if emptyAction}
          {@render emptyAction()}
        {/if}
      {/snippet}
    </PickerEmptyState>
  {:else}
    <div class="max-h-60 overflow-y-auto">
      <div class={pickerListTheme({ variant })}>
        {#if filteredItems.length === 0}
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
        {:else}
          {#each filteredItems as item, index (index)}
            {@render children({
              item,
              index,
              highlighted: index === highlightedIndex,
            })}
          {/each}
        {/if}
      </div>
    </div>

    {#if onCreateNew && createFooter}
      <PickerCreateFooter onclick={onCreateNew}>
        {@render createFooter()}
      </PickerCreateFooter>
    {/if}
  {/if}
</div>
