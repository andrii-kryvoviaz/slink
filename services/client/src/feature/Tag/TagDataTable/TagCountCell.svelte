<script lang="ts">
  import { goto } from '$app/navigation';
  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { tagFilterUtils } from '@slink/lib/utils/tag/tagFilterUrl';

  interface Props {
    count: number;
    type: 'images' | 'children';
    tag?: Tag;
  }

  let { count, type, tag }: Props = $props();

  const isClickable = $derived(type === 'images' && count > 0 && tag);

  const handleClick = () => {
    if (!isClickable || !tag) {
      return;
    }

    const historyUrl = tagFilterUtils.buildHistoryUrl(tag);
    goto(historyUrl);
  };

  const handleKeydown = (event: KeyboardEvent) => {
    if (!isClickable) {
      return;
    }

    if (event.key === 'Enter' || event.key === ' ') {
      event.preventDefault();
      handleClick();
    }
  };
</script>

{#if count > 0}
  {#if isClickable}
    <button
      onclick={handleClick}
      onkeydown={handleKeydown}
      class="group inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-sm font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-500/10 hover:bg-blue-100 dark:hover:bg-blue-500/20 border border-blue-200/50 dark:border-blue-500/20 hover:border-blue-300 dark:hover:border-blue-500/30 transition-all duration-200"
    >
      {count}
      <Icon
        icon="lucide:arrow-right"
        class="h-3.5 w-3.5 opacity-60 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all duration-200"
      />
    </button>
  {:else}
    <span class="text-sm font-medium text-slate-600 dark:text-slate-400">
      {count}
    </span>
  {/if}
{:else}
  <span class="text-sm text-slate-400 dark:text-slate-500">—</span>
{/if}
