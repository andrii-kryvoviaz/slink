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
      class="group inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-sm font-medium text-info bg-info-fill/8 hover:bg-info-fill/15 border border-info-border/50 hover:border-info-border transition-all duration-200"
    >
      {count}
      <Icon
        icon="lucide:arrow-right"
        class="h-3.5 w-3.5 opacity-60 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all duration-200"
      />
    </button>
  {:else}
    <span class="text-sm font-medium text-muted-foreground">
      {count}
    </span>
  {/if}
{:else}
  <span class="text-sm text-foreground-subtle">—</span>
{/if}
