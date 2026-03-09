<script lang="ts">
  import { Button } from '@slink/ui/components/button';
  import { cva } from 'class-variance-authority';
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';
  import { fly } from 'svelte/transition';

  const selectAllCheckboxVariants = cva(
    'flex items-center justify-center w-5 h-5 rounded border-2 transition-all duration-150',
    {
      variants: {
        state: {
          none: 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600',
          indeterminate: 'bg-blue-500 border-blue-500',
          all: 'bg-blue-500 border-blue-500',
        },
      },
      defaultVariants: {
        state: 'none',
      },
    },
  );

  interface Props {
    selectedCount: number;
    totalCount: number;
    onSelectAll: () => void;
    onDeselectAll: () => void;
    onCancel: () => void;
    actions?: Snippet;
  }

  let {
    selectedCount,
    totalCount,
    onSelectAll,
    onDeselectAll,
    onCancel,
    actions,
  }: Props = $props();

  const isAllSelected = $derived(
    selectedCount > 0 && selectedCount === totalCount,
  );
  const isIndeterminate = $derived(
    selectedCount > 0 && selectedCount < totalCount,
  );

  const checkboxState = $derived.by(() => {
    if (isAllSelected) return 'all' as const;
    if (isIndeterminate) return 'indeterminate' as const;
    return 'none' as const;
  });

  const handleCheckboxChange = () => {
    if (isAllSelected || isIndeterminate) {
      onDeselectAll();
    } else {
      onSelectAll();
    }
  };
</script>

<div
  in:fly={{ y: 20, duration: 200 }}
  out:fly={{ y: 20, duration: 150 }}
  class="fixed bottom-4 sm:bottom-6 left-1/2 -translate-x-1/2 z-50 w-[calc(100vw-2rem)] sm:w-auto"
>
  <div
    class="flex items-center gap-1.5 sm:gap-4 px-2.5 sm:px-4 py-2.5 sm:py-3 max-w-full bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl border border-gray-200/50 dark:border-gray-700/50 rounded-2xl shadow-lg shadow-gray-200/50 dark:shadow-gray-900/50"
  >
    <button
      type="button"
      onclick={handleCheckboxChange}
      class="flex items-center gap-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg px-1 sm:px-2 py-1 transition-colors"
      aria-label={isAllSelected ? 'Deselect all' : 'Select all'}
    >
      <div class={selectAllCheckboxVariants({ state: checkboxState })}>
        {#if isAllSelected}
          <Icon icon="heroicons:check" class="w-3.5 h-3.5 text-white" />
        {:else if isIndeterminate}
          <Icon icon="heroicons:minus" class="w-3.5 h-3.5 text-white" />
        {/if}
      </div>
    </button>

    <span
      class="text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap"
    >
      {selectedCount} selected
    </span>

    <div class="h-6 w-px bg-gray-200 dark:bg-gray-700"></div>

    {#if actions}
      {@render actions()}
    {/if}

    <Button
      variant="ghost"
      size="sm"
      rounded="full"
      onclick={onCancel}
      class="text-gray-600 dark:text-gray-400"
    >
      <Icon icon="lucide:x" class="w-4 h-4 sm:hidden" />
      <span class="hidden sm:inline">Cancel</span>
    </Button>
  </div>
</div>
