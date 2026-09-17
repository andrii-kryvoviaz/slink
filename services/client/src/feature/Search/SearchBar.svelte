<script lang="ts">
  import {
    searchBarDropdownChevron,
    searchBarField,
  } from '@slink/feature/Search/SearchBar.theme';
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
    disabled?: boolean;
    onsearch?: (event: { searchTerm: string; searchBy: SearchBy }) => void;
    onclear?: () => void;
  }

  let {
    searchTerm = $bindable(''),
    searchBy = $bindable('user'),
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

  $effect(() => {
    if (hasHashtags(searchTerm) && searchBy !== 'hashtag') {
      searchBy = 'hashtag';
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

  const clearTerm = () => {
    if (searchTerm) {
      searchTerm = '';
    }
  };

  const currentOption = $derived(searchOptions[searchBy]);
</script>

<Filter.Search
  bind:searchTerm
  {disabled}
  placeholder={currentOption.placeholder}
  variant="neon"
  size="md"
  rounded="lg"
  debounceMs={1000}
  onSearch={(term) => onsearch?.({ searchTerm: term, searchBy })}
  onClear={onclear}
  class={searchBarField({ focused: dropdownOpen })}
  inputClass="px-2"
  onEnter={(event) => {
    event.preventDefault();
    fireSearch();
  }}
  onEscape={clearTerm}
>
  {#snippet trailing()}
    <Filter.Divider class="bg-border-strong" />

    <DropdownSimple
      bind:open={dropdownOpen}
      triggerClass="w-fit"
      contentProps={{ align: 'end', sideOffset: 12 }}
    >
      {#snippet trigger(triggerProps)}
        <button
          {...triggerProps}
          class={cn(
            'flex items-center gap-1 px-1.5 sm:px-2 py-1 text-xs font-medium',
            'text-foreground-muted hover:text-foreground',
            'hover:bg-hover rounded-md transition-colors duration-150',
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
