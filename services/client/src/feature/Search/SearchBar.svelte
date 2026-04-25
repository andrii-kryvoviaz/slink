<script lang="ts">
  import {
    searchBarBack,
    searchBarBackdrop,
    searchBarContainer,
    searchBarDropdownChevron,
    searchBarField,
    searchBarTrigger,
  } from '@slink/feature/Search/SearchBar.theme';
  import { Button } from '@slink/ui/components/button';
  import {
    DropdownSimple,
    DropdownSimpleItem,
  } from '@slink/ui/components/dropdown-simple';
  import * as Filter from '@slink/ui/components/filter';

  import { hasHashtags } from '$lib/utils/text/hashtag';
  import Icon from '@iconify/svelte';

  import { cn } from '@slink/utils/ui/index.js';

  type SearchBy = 'user' | 'description' | 'hashtag';

  interface SearchOption {
    label: string;
    short: string;
    icon: string;
    placeholder: string;
  }

  interface Props {
    searchTerm?: string;
    searchBy?: SearchBy;
    placeholder?: string;
    disabled?: boolean;
    onsearch?: (event: { searchTerm: string; searchBy: SearchBy }) => void;
    onclear?: () => void;
  }

  let {
    searchTerm = $bindable(''),
    searchBy = $bindable('user'),
    placeholder = 'Search images...',
    disabled = false,
    onsearch,
    onclear,
  }: Props = $props();

  const searchOptions: Record<SearchBy, SearchOption> = {
    user: {
      label: 'Search by User',
      short: 'User',
      icon: 'ph:user',
      placeholder: 'Search users...',
    },
    description: {
      label: 'Search by Description',
      short: 'Description',
      icon: 'ph:text-align-left',
      placeholder: 'Search descriptions...',
    },
    hashtag: {
      label: 'Search by Hashtag',
      short: 'Hashtag',
      icon: 'ph:hash',
      placeholder: 'Search hashtags... (e.g., #nature)',
    },
  };

  let dropdownOpen = $state(false);
  let expanded = $state(false);
  let searchRef:
    | { focus: () => void; blur: () => void; clear: () => void }
    | undefined;

  $effect(() => {
    if (hasHashtags(searchTerm) && searchBy !== 'hashtag') {
      searchBy = 'hashtag';
    }
  });

  $effect(() => {
    if (expanded) {
      requestAnimationFrame(() => searchRef?.focus());
    }
  });

  const fireSearch = () => {
    const term = searchTerm.trim();
    if (term) onsearch?.({ searchTerm: term, searchBy });
  };

  const selectSearchBy = (value: SearchBy) => {
    searchBy = value;
    dropdownOpen = false;
    fireSearch();
  };

  const openMobile = () => {
    expanded = true;
  };

  const closeMobile = () => {
    expanded = false;
    searchRef?.blur();
  };

  const handleEscape = (event: KeyboardEvent) => {
    if (searchTerm) {
      searchTerm = '';
      return;
    }
    if (expanded) {
      event.preventDefault();
      closeMobile();
    }
  };

  const currentOption = $derived(searchOptions[searchBy]);
  const dynamicPlaceholder = $derived(currentOption.placeholder ?? placeholder);
</script>

{#if !expanded}
  <Button
    variant="glass"
    size="sm"
    rounded="full"
    class={searchBarTrigger()}
    onclick={openMobile}
  >
    {#snippet children()}
      <Icon icon="ph:magnifying-glass" class="h-3 w-3 sm:h-4 sm:w-4" />
      <span>Search</span>
    {/snippet}
  </Button>
{/if}

{#if expanded}
  <button
    type="button"
    aria-label="Close search"
    tabindex="-1"
    class={searchBarBackdrop()}
    onclick={closeMobile}
  ></button>
{/if}

<div class={searchBarContainer({ expanded })}>
  {#if expanded}
    <Button
      variant="glass"
      size="sm"
      rounded="full"
      aria-label="Close search"
      class={searchBarBack()}
      onclick={closeMobile}
    >
      <Icon icon="ph:arrow-left" class="h-4 w-4" />
    </Button>
  {/if}

  <Filter.Search
    bind:this={searchRef}
    bind:searchTerm
    {disabled}
    placeholder={dynamicPlaceholder}
    variant="pill"
    size="sm"
    rounded="full"
    debounceMs={1000}
    onSearch={(term) => onsearch?.({ searchTerm: term, searchBy })}
    onClear={onclear}
    class={searchBarField({ focused: dropdownOpen })}
    inputClass="px-2"
    onEnter={(event) => {
      event.preventDefault();
      fireSearch();
    }}
    onEscape={handleEscape}
  >
    {#snippet trailing()}
      <div class="w-px h-4 bg-gray-200 dark:bg-gray-700 shrink-0"></div>

      <DropdownSimple
        bind:open={dropdownOpen}
        triggerClass="w-fit"
        contentProps={{ align: 'end', sideOffset: 12 }}
      >
        {#snippet trigger(triggerProps)}
          <button
            {...triggerProps}
            class={cn(
              'flex items-center gap-1 px-1.5 sm:px-2 py-0.5 text-xs font-medium',
              'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100',
              'hover:bg-gray-50 dark:hover:bg-gray-800 rounded-r-full transition-colors duration-150',
              'whitespace-nowrap shrink-0',
            )}
            type="button"
            aria-label="Search options"
            {disabled}
          >
            <span class="hidden sm:block">{currentOption.short}</span>
            <Icon
              icon="ph:caret-down"
              class={searchBarDropdownChevron({ open: dropdownOpen })}
            />
          </button>
        {/snippet}

        {#each Object.entries(searchOptions) as [value, option] (value)}
          <DropdownSimpleItem
            on={{ click: () => selectSearchBy(value as SearchBy) }}
          >
            {#snippet icon()}
              <Icon icon={option.icon} class="h-4 w-4" />
            {/snippet}
            {option.label}
          </DropdownSimpleItem>
        {/each}
      </DropdownSimple>
    {/snippet}
  </Filter.Search>
</div>
