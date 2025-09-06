<script lang="ts">
  import { hasHashtags } from '$lib/utils/text/hashtag';
  import { debounce } from '$lib/utils/time/debounce';
  import { className as cn } from '$lib/utils/ui/className';
  import Icon from '@iconify/svelte';

  interface Props {
    searchTerm?: string;
    searchBy?: 'user' | 'description' | 'hashtag';
    placeholder?: string;
    disabled?: boolean;
    onsearch?: (event: { searchTerm: string; searchBy: string }) => void;
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

  const searchOptions = [
    { value: 'user', label: 'Search by User', icon: 'ph:user' },
    {
      value: 'description',
      label: 'Search by Description',
      icon: 'ph:text-align-left',
    },
    {
      value: 'hashtag',
      label: 'Search by Hashtag',
      icon: 'ph:hash',
    },
  ];

  let showOptions = $state(false);
  let searchInput: HTMLInputElement;

  const debouncedSearch = debounce(() => {
    if (searchTerm.trim()) {
      onsearch?.({ searchTerm: searchTerm.trim(), searchBy });
    }
  }, 1000);

  let previousSearchTerm = '';
  $effect(() => {
    if (searchTerm !== previousSearchTerm) {
      previousSearchTerm = searchTerm;

      if (hasHashtags(searchTerm) && searchBy !== 'hashtag') {
        searchBy = 'hashtag';
      }

      if (searchTerm.trim()) {
        debouncedSearch();
      } else if (searchTerm === '') {
        debouncedSearch.cancel();
        onclear?.();
      }
    }
  });

  function handleClear() {
    debouncedSearch.cancel();
    searchTerm = '';
    onclear?.();
    searchInput?.focus();
  }

  function handleKeydown(event: KeyboardEvent) {
    if (event.key === 'Enter') {
      debouncedSearch.cancel();
      if (searchTerm.trim()) {
        onsearch?.({ searchTerm: searchTerm.trim(), searchBy });
      }
    } else if (event.key === 'Escape') {
      handleClear();
    }
  }

  function selectSearchBy(value: string) {
    searchBy = value as 'user' | 'description' | 'hashtag';
    showOptions = false;
    if (searchTerm.trim()) {
      debouncedSearch.cancel();
      onsearch?.({ searchTerm: searchTerm.trim(), searchBy });
    }
  }

  function toggleOptions() {
    showOptions = !showOptions;
  }

  function handleClickOutside(event: MouseEvent) {
    const target = event.target as HTMLElement;
    if (!target.closest('.search-bar')) {
      showOptions = false;
    }
  }

  let currentOption = $derived(
    searchOptions
      .find((opt) => opt.value === searchBy)
      ?.label.replace('Search by ', '') || 'User',
  );

  const placeholderMap = {
    hashtag: 'Search hashtags... (e.g., #nature)',
    description: 'Search descriptions...',
    user: 'Search users...',
  };

  let dynamicPlaceholder = $derived(placeholderMap[searchBy] ?? placeholder);
</script>

<svelte:window onclick={handleClickOutside} />

<div class="relative z-40" class:opacity-60={disabled}>
  <div
    class={cn(
      'search-bar inline-flex items-center h-8 px-2 sm:px-3 text-xs font-medium relative',
      'bg-white/90 border border-gray-200/50 text-gray-600 shadow-sm',
      'hover:text-gray-900 hover:bg-white/95 hover:border-gray-300/60 hover:shadow-md hover:shadow-gray-200/30',
      'transition-all duration-200 rounded-full min-w-[160px] sm:min-w-[200px] max-w-[200px] sm:max-w-[280px]',
      'dark:bg-gray-900/90 dark:border-gray-700/50 dark:text-gray-400 dark:shadow-black/10',
      'dark:hover:text-gray-100 dark:hover:bg-gray-800/95 dark:hover:border-gray-600/60 dark:hover:shadow-black/20',
      showOptions && 'ring-2 ring-blue-500/20 border-blue-300/60 shadow-md',
    )}
  >
    <Icon
      icon={searchBy === 'hashtag' ? 'ph:hash' : 'ph:magnifying-glass'}
      class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-gray-400 dark:text-gray-500 flex-shrink-0"
    />

    <input
      bind:this={searchInput}
      bind:value={searchTerm}
      onkeydown={handleKeydown}
      placeholder={dynamicPlaceholder}
      {disabled}
      class={cn(
        'flex-1 bg-transparent border-none outline-none text-xs px-1 sm:px-2 py-1 min-w-0',
        'text-gray-700 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500',
      )}
      type="text"
      autocomplete="off"
      spellcheck="false"
    />

    {#if searchTerm.trim()}
      <button
        onclick={handleClear}
        class={cn(
          'p-0.5 mr-0.5 sm:mr-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300',
          'hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors duration-150 flex-shrink-0',
        )}
        type="button"
        aria-label="Clear search"
        {disabled}
      >
        <Icon icon="ph:x" class="w-2.5 h-2.5 sm:w-3 sm:h-3" />
      </button>
    {/if}

    <div class="w-px h-4 bg-gray-200 dark:bg-gray-700 flex-shrink-0"></div>

    <button
      onclick={toggleOptions}
      class={cn(
        'flex items-center gap-1 px-1.5 sm:px-2 py-1 text-xs font-medium',
        'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100',
        'hover:bg-gray-50 dark:hover:bg-gray-800 rounded-r-full transition-colors duration-150',
        'whitespace-nowrap flex-shrink-0',
      )}
      type="button"
      aria-label="Search options"
      {disabled}
    >
      <span class="hidden sm:block">{currentOption}</span>
      <Icon
        icon="ph:caret-down"
        class={cn(
          'w-3 h-3 transition-transform duration-200 flex-shrink-0',
          showOptions && 'rotate-180',
        )}
      />
    </button>

    {#if showOptions}
      <div
        class={cn(
          'absolute top-full right-0 mt-2 min-w-[160px] overflow-hidden rounded-xl p-1 shadow-xl shadow-black/10 dark:shadow-black/25 border',
          'z-[9999] bg-white dark:bg-gray-900/95 backdrop-blur-sm border-gray-200/80 dark:border-gray-700/80',
          'animate-in fade-in-0 zoom-in-95 duration-150',
        )}
      >
        {#each searchOptions as option}
          <button
            onclick={() => selectSearchBy(option.value)}
            class={cn(
              'flex items-center gap-3 w-full px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-150 cursor-pointer',
              'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/20',
              searchBy === option.value
                ? 'text-blue-600 dark:text-blue-300 bg-blue-100 dark:bg-blue-800/40 shadow-sm'
                : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800/60 hover:text-gray-900 dark:hover:text-gray-100',
            )}
            type="button"
          >
            <Icon
              icon={option.icon}
              class="h-4 w-4 flex-shrink-0 text-gray-500 dark:text-gray-400"
            />
            <span class="flex-1 text-left">{option.label}</span>
          </button>
        {/each}
      </div>
    {/if}
  </div>
</div>
