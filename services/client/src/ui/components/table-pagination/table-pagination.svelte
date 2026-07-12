<script lang="ts">
  import { Button } from '@slink/ui/components/button';
  import {
    HoverCard,
    HoverCardContent,
    HoverCardTrigger,
  } from '@slink/ui/components/hover-card';
  import { tv } from 'tailwind-variants';

  import Icon from '@iconify/svelte';

  const containerTheme = tv({
    base: 'flex items-center gap-1 rounded-lg border',
    variants: {
      variant: {
        default:
          'bg-white/80 dark:bg-gray-900/80 border-gray-200/60 dark:border-gray-700/60',
        neutral:
          'bg-white/50 dark:bg-gray-900/20 border-gray-200/70 dark:border-gray-700/50',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  });

  const pageInputTheme = tv({
    base: 'h-7 w-8 text-xs font-medium text-center rounded-md border outline-none transition-all duration-200 focus:outline-none hover:border-gray-300 dark:hover:border-gray-600',
    variants: {
      variant: {
        default: 'bg-gray-100/80 dark:bg-gray-800/80',
        neutral: 'bg-gray-100/70 dark:bg-gray-800/50',
      },
      status: {
        default:
          'border-gray-200/60 dark:border-gray-700/60 text-foreground placeholder:text-gray-400 dark:placeholder:text-gray-500',
        error: 'border-red-500 text-red-600 placeholder:text-red-400',
      },
    },
    compoundVariants: [
      {
        variant: 'neutral',
        status: 'default',
        class: 'border-gray-200/70 dark:border-gray-700/50',
      },
    ],
    defaultVariants: {
      variant: 'default',
      status: 'default',
    },
  });

  const pageButtonTheme = tv({
    base: 'text-xs sm:text-sm font-medium transition-all duration-200 w-8 h-7 flex items-center justify-center rounded-md tabular-nums',
    variants: {
      variant: {
        default: '',
        neutral: '',
      },
      status: {
        interactive:
          'cursor-pointer hover:bg-gray-100/80 dark:hover:bg-gray-700/60 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 active:scale-95 transform',
        static: 'cursor-default text-gray-400 dark:text-gray-500',
      },
    },
    compoundVariants: [
      {
        variant: 'neutral',
        status: 'interactive',
        class:
          'hover:bg-gray-100/80 dark:hover:bg-gray-800/40 focus:ring-gray-400/40 dark:focus:ring-gray-500/40',
      },
      {
        variant: 'neutral',
        status: 'static',
        class: 'text-gray-500 dark:text-gray-400',
      },
    ],
    defaultVariants: {
      variant: 'default',
      status: 'static',
    },
  });

  type TablePaginationVariant = 'default' | 'neutral';

  interface Props {
    variant?: TablePaginationVariant;
    currentPageIndex: number;
    totalPages: number;
    canPreviousPage: boolean;
    canNextPage: boolean;
    totalItems?: number;
    pageSize?: number;
    loading?: boolean;
    disablePageSelection?: boolean;
    onPageChange?: (page: number) => void;
    additionalInfo?: string;
  }

  let {
    variant = 'default',
    currentPageIndex,
    totalPages,
    canPreviousPage,
    canNextPage,
    totalItems,
    pageSize,
    loading = false,
    disablePageSelection = false,
    onPageChange,
    additionalInfo,
  }: Props = $props();

  let pageInput = $state('');
  let isInputFocused = $state(false);
  let hasError = $state(false);
  let inputRef = $state<HTMLInputElement>();
  let triggerWidth = $state(0);

  const currentPage = $derived(currentPageIndex + 1);
  const effectivePageSize = $derived(pageSize ?? 10);
  const isPageEditable = $derived(
    !disablePageSelection && !!(onPageChange && totalPages > 1 && !loading),
  );
  const hasItems = $derived(!loading && !!totalItems && totalItems > 0);

  const startItem = $derived.by(() => {
    if (!totalItems) return 0;
    return (currentPage - 1) * effectivePageSize + 1;
  });

  const endItem = $derived.by(() => {
    if (!totalItems) return 0;
    return Math.min(currentPage * effectivePageSize, totalItems);
  });

  const progress = $derived.by(() => {
    if (!totalItems || totalItems <= 0) return 0;
    return Math.round((endItem / totalItems) * 100);
  });

  const inputStatus = $derived.by<'default' | 'error'>(() => {
    if (hasError) return 'error';
    return 'default';
  });

  const pageButtonStatus = $derived.by<'interactive' | 'static'>(() => {
    if (isPageEditable) return 'interactive';
    return 'static';
  });

  const inputClass = $derived(pageInputTheme({ variant, status: inputStatus }));
  const pageButtonClass = $derived(
    pageButtonTheme({ variant, status: pageButtonStatus }),
  );
  const containerClass = $derived(containerTheme({ variant }));

  const navButtonVariant = $derived.by<'transparent' | 'ghost'>(() => {
    if (variant === 'neutral') return 'ghost';
    return 'transparent';
  });

  const navButtonClass = $derived.by(() => {
    if (variant === 'neutral') {
      return 'h-8 px-2 text-xs border-0 hover:bg-gray-100/80 dark:hover:bg-gray-800/40';
    }
    return 'h-8 px-2 text-xs border-0';
  });

  const hoverCardVariant = $derived.by<'glass' | 'default'>(() => {
    if (variant === 'neutral') return 'default';
    return 'glass';
  });

  const pageButtonTitle = $derived.by(() => {
    if (!isPageEditable) return undefined;
    return 'Click to enter page number';
  });

  const pageButtonAriaLabel = $derived.by(() => {
    if (!isPageEditable) return `Current page ${currentPage}`;
    return `Current page ${currentPage}. Click to enter page number`;
  });

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

  const validatePageNumber = (value: string): boolean => {
    if (value === '') return true;
    const num = parseInt(value, 10);
    return !isNaN(num) && num >= 1 && num <= totalPages;
  };

  const handlePageInputChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    pageInput = target.value;
    hasError = !validatePageNumber(target.value);
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
    if (!isPageEditable) return;
    pageInput = currentPage.toString();
    isInputFocused = true;
  };

  $effect(() => {
    if (isInputFocused && inputRef) {
      inputRef.focus();
      inputRef.select();
    }
  });
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

<div class="flex items-center gap-3 flex-grow">
  <HoverCard>
    <HoverCardTrigger>
      {#snippet child({ props })}
        <div {...props} bind:clientWidth={triggerWidth} class={containerClass}>
          <Button
            variant={navButtonVariant}
            size="sm"
            onclick={handlePreviousPage}
            disabled={!canPreviousPage || loading}
            class="{navButtonClass} rounded-l-lg rounded-r-none"
            title="Previous page"
          >
            <Icon icon="heroicons:chevron-left" class="h-4 w-4" />
          </Button>

          <div class="flex items-center gap-1 px-2">
            {#if isInputFocused && onPageChange}
              <form
                onsubmit={handlePageInputSubmit}
                class="flex items-center gap-1"
              >
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
                    class={inputClass}
                    placeholder={currentPage.toString()}
                    aria-label="Go to page"
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
                  <span
                    class="text-xs sm:text-sm text-gray-300 dark:text-gray-600"
                    >/</span
                  >
                  <span
                    class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 tabular-nums w-8 text-center"
                    >{totalPages}</span
                  >
                {/if}
              </form>
            {:else}
              <button
                type="button"
                onclick={handlePageClick}
                disabled={!isPageEditable}
                class={pageButtonClass}
                title={pageButtonTitle}
                aria-label={pageButtonAriaLabel}
              >
                {currentPage}
              </button>
              {#if totalPages > 1}
                <span
                  class="text-xs sm:text-sm text-gray-300 dark:text-gray-600"
                  >/</span
                >
                <span
                  class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 tabular-nums w-8 text-center"
                  >{totalPages}</span
                >
              {/if}
            {/if}
          </div>

          <Button
            variant={navButtonVariant}
            size="sm"
            onclick={handleNextPage}
            disabled={!canNextPage || loading}
            class="{navButtonClass} rounded-r-lg rounded-l-none"
            title="Next page"
          >
            <Icon icon="heroicons:chevron-right" class="h-4 w-4" />
          </Button>
        </div>
      {/snippet}
    </HoverCardTrigger>
    {#if hasItems}
      <HoverCardContent
        variant={hoverCardVariant}
        size="sm"
        rounded="lg"
        width="auto"
        side="bottom"
        sideOffset={8}
        align="start"
        style="width: {triggerWidth}px"
      >
        <div class="flex flex-col gap-2">
          <div class="flex items-center justify-between tabular-nums">
            <span class="text-xs text-gray-600 dark:text-gray-300">
              {startItem}–{endItem}
              <span class="text-gray-400 dark:text-gray-500">of</span>
              {totalItems} items
            </span>
            <span class="text-xs text-gray-400 dark:text-gray-500"
              >{progress}%</span
            >
          </div>
          <div
            class="h-1 rounded-full bg-gray-200/60 dark:bg-gray-700/40 overflow-hidden"
          >
            <div
              class="h-full rounded-full bg-gray-400 dark:bg-gray-500 transition-all duration-300 ease-out"
              style="width: {progress}%"
            ></div>
          </div>
          {#if additionalInfo}
            <span class="text-xs text-gray-400 dark:text-gray-500"
              >{additionalInfo}</span
            >
          {/if}
        </div>
      </HoverCardContent>
    {/if}
  </HoverCard>
</div>
