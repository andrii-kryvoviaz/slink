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
          none: 'bg-input border-border-strong',
          indeterminate: 'bg-primary-solid border-primary-solid',
          all: 'bg-primary-solid border-primary-solid',
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
    class="flex items-center gap-1.5 sm:gap-4 px-2.5 sm:px-4 py-2.5 sm:py-3 max-w-full bg-card/80 backdrop-blur-xl border border-border/50 rounded-2xl shadow-lg shadow-border/50 dark:shadow-surface-inverse/50"
  >
    <button
      type="button"
      onclick={handleCheckboxChange}
      class="flex items-center gap-2 hover:bg-hover rounded-lg px-1 sm:px-2 py-1 transition-colors"
      aria-label={isAllSelected ? 'Deselect all' : 'Select all'}
    >
      <div class={selectAllCheckboxVariants({ state: checkboxState })}>
        {#if isAllSelected}
          <Icon
            icon="heroicons:check"
            class="w-3.5 h-3.5 text-on-primary-solid"
          />
        {:else if isIndeterminate}
          <Icon
            icon="heroicons:minus"
            class="w-3.5 h-3.5 text-on-primary-solid"
          />
        {/if}
      </div>
    </button>

    <span class="text-sm font-medium text-foreground-soft whitespace-nowrap">
      {selectedCount} selected
    </span>

    <div class="h-6 w-px bg-border"></div>

    {#if actions}
      {@render actions()}
    {/if}

    <Button
      variant="ghost"
      size="sm"
      rounded="full"
      onclick={onCancel}
      class="text-foreground-muted"
    >
      <Icon icon="lucide:x" class="w-4 h-4 sm:hidden" />
      <span class="hidden sm:inline">Cancel</span>
    </Button>
  </div>
</div>
