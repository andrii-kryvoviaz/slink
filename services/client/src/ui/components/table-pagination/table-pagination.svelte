<script lang="ts">
  import { Button } from '@slink/ui/components/button';

  import { className as cn } from '$lib/utils/ui/className';
  import Icon from '@iconify/svelte';

  interface Props {
    currentPageIndex: number;
    totalPages: number;
    canPreviousPage: boolean;
    canNextPage: boolean;
    totalItems?: number;
    pageSize?: number;
    loading?: boolean;
    onPageChange?: (page: number) => void;
    additionalInfo?: string;
  }

  let {
    currentPageIndex,
    totalPages,
    canPreviousPage,
    canNextPage,
    totalItems,
    pageSize,
    loading = false,
    onPageChange,
    additionalInfo,
  }: Props = $props();

  let pageInput = $state('');
  let isInputFocused = $state(false);
  let hasError = $state(false);
  let inputRef = $state<HTMLInputElement>();

  const currentPage = $derived(currentPageIndex + 1);

  const handlePreviousPage = () => {
    if (canPreviousPage && !loading && onPageChange) {
      onPageChange(currentPage - 1);
    }
  };

  const handleNextPage = () => {
    if (canNextPage && !loading && onPageChange) {
      onPageChange(currentPage + 1);
    }
  };

  const handleGoToPage = (page: number) => {
    if (!loading && onPageChange) {
      onPageChange(page);
    }
  };

  $effect(() => {
    if (isInputFocused && inputRef) {
      inputRef.focus();
      inputRef.select();
    }
  });

  const validatePageNumber = (value: string): boolean => {
    if (value === '') return true;
    const num = parseInt(value, 10);
    return !isNaN(num) && num >= 1 && num <= totalPages;
  };

  const handlePageInputChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    const value = target.value;
    pageInput = value;
    hasError = !validatePageNumber(value);
  };

  const handlePageInputSubmit = (e: Event) => {
    e.preventDefault();
    const pageNumber = parseInt(pageInput, 10);

    if (isNaN(pageNumber) || pageNumber < 1 || pageNumber > totalPages) {
      hasError = true;
      inputRef?.focus();
      return;
    }

    if (pageNumber === currentPage) {
      handleCancelInput();
      return;
    }

    if (onPageChange) {
      handleGoToPage(pageNumber);
      handleCancelInput();
    }
  };

  const handleCancelInput = () => {
    pageInput = '';
    isInputFocused = false;
    hasError = false;
  };

  const handleInputKeyDown = (e: KeyboardEvent) => {
    if (e.key === 'Escape') {
      handleCancelInput();
    }
  };

  const handleInputBlur = () => {
    setTimeout(() => {
      handleCancelInput();
    }, 150);
  };

  const handlePageClick = () => {
    if (!onPageChange || loading || totalPages <= 1) return;
    pageInput = currentPage.toString();
    isInputFocused = true;
  };

  const startItem = $derived(
    totalItems ? (currentPage - 1) * (pageSize || 10) + 1 : 0,
  );
  const endItem = $derived(
    totalItems ? Math.min(currentPage * (pageSize || 10), totalItems) : 0,
  );
</script>

<svelte:window
  onkeydown={(e) => {
    if (!loading) {
      if (e.key === 'ArrowLeft') {
        e.preventDefault();
        handlePreviousPage();
      } else if (e.key === 'ArrowRight') {
        e.preventDefault();
        handleNextPage();
      }
    }
  }}
/>

<div
  class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 flex-grow"
>
  {#if !loading && totalItems && totalItems > 0}
    <div class="text-xs sm:text-sm text-muted-foreground order-2 sm:order-1">
      <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-4">
        <span>
          {startItem}–{endItem} of {totalItems} items
        </span>
        {#if additionalInfo}
          <span class="text-muted-foreground/80">
            {additionalInfo}
          </span>
        {/if}
      </div>
    </div>
  {:else if loading || !totalItems || totalItems === 0}
    <div class="hidden sm:block"></div>
  {/if}

  <div
    class="flex items-center justify-center sm:justify-end gap-1 order-1 sm:order-2"
  >
    <Button
      variant="ghost"
      size="sm"
      onclick={handlePreviousPage}
      disabled={!canPreviousPage || loading}
      class="h-8 px-2 text-xs"
      title="Previous page"
    >
      <Icon icon="heroicons:chevron-left" class="h-4 w-4" />
    </Button>

    <div class="flex items-center gap-1 px-2 pl-0">
      {#if isInputFocused && onPageChange}
        <form onsubmit={handlePageInputSubmit} class="flex items-center gap-1">
          <div class="relative">
            <input
              bind:this={inputRef}
              type="text"
              inputmode="numeric"
              pattern="[0-9]*"
              bind:value={pageInput}
              oninput={handlePageInputChange}
              onblur={handleInputBlur}
              onkeydown={handleInputKeyDown}
              class={cn(
                'h-7 w-12 text-xs font-medium text-center',
                'bg-transparent border-0 border-b-2 rounded-none',
                'outline-none transition-all duration-200',
                'focus:border-b-2 focus:outline-none',
                hasError
                  ? 'border-b-red-500 text-red-600 placeholder:text-red-400'
                  : 'border-b-primary text-foreground placeholder:text-muted-foreground',
                'hover:border-b-primary/70',
              )}
              placeholder={currentPage.toString()}
              aria-label="Go to page"
              style="background: transparent; box-shadow: none;"
            />
            {#if hasError}
              <div
                class="absolute -bottom-6 left-1/2 -translate-x-1/2 text-xs text-red-500 whitespace-nowrap animate-in fade-in-0 slide-in-from-top-1 duration-200"
              >
                1-{totalPages}
              </div>
            {/if}
          </div>
          {#if totalPages > 1}
            <span class="text-xs sm:text-sm text-muted-foreground">of</span>
            <span class="text-xs sm:text-sm text-muted-foreground"
              >{totalPages}</span
            >
          {/if}
        </form>
      {:else}
        <button
          type="button"
          onclick={handlePageClick}
          disabled={loading || !onPageChange || totalPages <= 1}
          class={cn(
            'text-xs sm:text-sm font-medium transition-all duration-200',
            'min-w-[2rem] h-7 flex items-center justify-center',
            'rounded-md relative group',
            onPageChange && totalPages > 1 && !loading
              ? [
                  'cursor-pointer',
                  'focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
                  'active:scale-95 transform',
                ]
              : 'cursor-default text-muted-foreground',
          )}
          title={onPageChange && totalPages > 1 && !loading
            ? 'Click to enter page number'
            : undefined}
          aria-label={onPageChange && totalPages > 1 && !loading
            ? `Current page ${currentPage}. Click to enter page number`
            : `Current page ${currentPage}`}
        >
          {currentPage}
          {#if onPageChange && totalPages > 1 && !loading}
            <div
              class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 w-0 h-0.5 bg-primary transition-all duration-200 group-hover:w-full"
            ></div>
          {/if}
        </button>
        {#if totalPages > 1}
          <span class="text-xs sm:text-sm text-muted-foreground">of</span>
          <span class="text-xs sm:text-sm text-muted-foreground"
            >{totalPages}</span
          >
        {/if}
      {/if}
    </div>

    <Button
      variant="ghost"
      size="sm"
      onclick={handleNextPage}
      disabled={!canNextPage || loading}
      class="h-8 px-2 text-xs"
      title="Next page"
    >
      <Icon icon="heroicons:chevron-right" class="h-4 w-4" />
    </Button>
  </div>
</div>
