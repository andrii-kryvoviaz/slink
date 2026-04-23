<script lang="ts">
  import type { Snippet } from 'svelte';

  import { plural } from '$lib/utils/i18n';
  import Icon from '@iconify/svelte';

  import { activeFilterBar } from './filter-bar.theme';

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

  const theme = activeFilterBar();
</script>

{#if visible && count > 0}
  <div class={theme.root()}>
    <Icon icon="heroicons:funnel-solid" class={theme.leadIcon()} />

    <span class={theme.summary()}>
      <span class={theme.summaryLabel()}>{label}</span>
      <span class={theme.summaryCount()}>
        {plural(count, countLabel)}
      </span>
    </span>

    {#if extras}
      {@render extras()}
    {/if}

    <button
      type="button"
      class={theme.clearButton()}
      onclick={onClear}
      {disabled}
      aria-label="Clear all filters"
    >
      <Icon icon="heroicons:x-mark-20-solid" class="w-3.5 h-3.5" />
      <span>{clearLabel}</span>
    </button>
  </div>
{/if}
