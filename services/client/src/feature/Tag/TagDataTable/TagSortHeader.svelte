<script lang="ts">
  import Icon from '@iconify/svelte';

  import type { TagSortKey, TagSortOrder } from './types';

  interface Props {
    label: string;
    sortKey: TagSortKey;
    activeSortKey?: TagSortKey | null;
    sortOrder?: TagSortOrder;
    onToggle?: (key: TagSortKey) => void;
  }

  let {
    label,
    sortKey,
    activeSortKey = null,
    sortOrder = 'asc',
    onToggle,
  }: Props = $props();

  const isActive = $derived(activeSortKey === sortKey);
  const icon = $derived(
    !isActive
      ? 'heroicons:chevron-up-down'
      : sortOrder === 'asc'
        ? 'heroicons:chevron-up'
        : 'heroicons:chevron-down',
  );

  const ariaLabel = $derived(
    isActive
      ? `Sort by ${label} (${sortOrder === 'asc' ? 'ascending' : 'descending'})`
      : `Sort by ${label}`,
  );

  const handleClick = () => {
    onToggle?.(sortKey);
  };
</script>

<button
  type="button"
  onclick={handleClick}
  class="inline-flex items-center gap-1 text-xs font-medium uppercase tracking-wider text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 transition-colors"
  aria-label={ariaLabel}
  disabled={!onToggle}
>
  <span>{label}</span>
  <Icon icon={icon} class="h-3.5 w-3.5" />
</button>
