<script lang="ts">
  import type { Snippet } from 'svelte';

  import { plural } from '$lib/utils/i18n';
  import Icon from '@iconify/svelte';

  interface Props {
    count: number;
    label?: string;
    countLabel?: [string, string];
    clearLabel?: string;
    onClear: () => void;
    disabled?: boolean;
    visible?: boolean;
    extras?: Snippet;
  }

  let {
    count,
    label = 'Filtering by',
    countLabel = ['# filter', '# filters'],
    clearLabel = 'Clear',
    onClear,
    disabled = false,
    visible = true,
    extras,
  }: Props = $props();
</script>

{#if visible && count > 0}
  <div
    class="mx-auto w-[calc(100%-1.5rem)] flex flex-wrap items-center gap-x-2 gap-y-1.5 px-3 py-2 rounded-b-lg bg-white dark:bg-gray-900/60 border border-t-0 border-gray-200/60 dark:border-white/10 shadow-sm text-sm"
  >
    <Icon
      icon="heroicons:funnel-solid"
      class="w-3.5 h-3.5 text-blue-500 dark:text-blue-400 shrink-0"
    />

    <span class="text-slate-600 dark:text-slate-300">
      <span class="hidden sm:inline">{label}</span>
      <span class="font-semibold text-blue-600 dark:text-blue-400">
        {plural(count, countLabel)}
      </span>
    </span>

    {#if extras}
      {@render extras()}
    {/if}

    <button
      type="button"
      class="ml-auto inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium text-slate-400 dark:text-slate-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
      onclick={onClear}
      {disabled}
      aria-label="Clear all filters"
    >
      <Icon icon="heroicons:x-mark-20-solid" class="w-3.5 h-3.5" />
      <span>{clearLabel}</span>
    </button>
  </div>
{/if}
