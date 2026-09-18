<script lang="ts">
  import {
    DropdownSimple,
    DropdownSimpleItem,
  } from '@slink/ui/components/dropdown-simple';
  import * as Filter from '@slink/ui/components/filter';

  import { hasHashtags } from '$lib/utils/text/hashtag';
  import Icon from '@iconify/svelte';

  import { cn } from '@slink/utils/ui/index.js';
  import type { SearchBy } from '@slink/utils/url';
  import { searchByValues } from '@slink/utils/url';

  import { searchBarDropdownChevron, searchBarField } from './SearchBar.theme';

  interface SearchOption {
    label: string;
    short: string;
    icon: string;
    placeholder: string;
  }

  interface Props {
    searchTerm?: string;
    searchBy?: SearchBy;
    onsearch?: (event: { searchTerm: string; searchBy: SearchBy }) => void;
    onclear?: () => void;
  }

  let { searchTerm, searchBy, onsearch, onclear }: Props = $props();

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

  let term = $derived(searchTerm ?? '');
  let scope = $derived(searchBy ?? 'user');
  let dropdownOpen = $state(false);

  $effect(() => {
    if (hasHashtags(term) && scope !== 'hashtag') {
      scope = 'hashtag';
    }
  });

  const fireSearch = () => {
    const trimmed = term.trim();
    if (trimmed) onsearch?.({ searchTerm: trimmed, searchBy: scope });
  };

  const selectSearchBy = (value: SearchBy) => {
    scope = value;
    dropdownOpen = false;
    fireSearch();
  };

  const clearTerm = () => {
    if (term) {
      term = '';
    }
  };

  const currentOption = $derived(searchOptions[scope]);
</script>

<Filter.Search
  bind:searchTerm={term}
  placeholder={currentOption.placeholder}
  variant="neon"
  size="md"
  rounded="lg"
  debounceMs={1000}
  onSearch={(value) => onsearch?.({ searchTerm: value, searchBy: scope })}
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
        >
          <span class="hidden sm:block">{currentOption.short}</span>
          <Icon
            icon="ph:caret-down"
            class={searchBarDropdownChevron({ open: dropdownOpen })}
          />
        </button>
      {/snippet}

      {#each searchByValues as value (value)}
        {@const option = searchOptions[value]}
        <DropdownSimpleItem on={{ click: () => selectSearchBy(value) }}>
          {#snippet icon()}
            <Icon icon={option.icon} class="h-4 w-4" />
          {/snippet}
          {option.label}
        </DropdownSimpleItem>
      {/each}
    </DropdownSimple>
  {/snippet}
</Filter.Search>
