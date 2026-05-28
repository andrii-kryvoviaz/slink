<script lang="ts">
  import type { Column } from '@tanstack/table-core';

  import Icon from '@iconify/svelte';

  import { sortableHeaderVariants } from './SortableHeader.theme';

  interface Props {
    label: string;
    column: Column<any, any>;
  }

  let { label, column }: Props = $props();

  const sortState = $derived(column.getIsSorted());

  const state = $derived.by(() => {
    if (sortState === 'asc') {
      return 'asc' as const;
    }
    if (sortState === 'desc') {
      return 'desc' as const;
    }
    return 'none' as const;
  });

  const slots = $derived(sortableHeaderVariants({ state }));

  function handleClick(event: MouseEvent) {
    column.getToggleSortingHandler()?.(event);
  }
</script>

<button type="button" class={slots.base()} onclick={handleClick}>
  {label}
  {#if sortState === 'asc'}
    <Icon icon="lucide:chevron-up" class={slots.icon()} />
  {:else if sortState === 'desc'}
    <Icon icon="lucide:chevron-down" class={slots.icon()} />
  {:else}
    <Icon icon="lucide:chevrons-up-down" class={slots.icon()} />
  {/if}
</button>
