<script lang="ts">
  import { Badge } from '@slink/feature/Text';

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

  const badgeVariant = $derived(type === 'images' ? 'blue' : 'emerald');
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
  <button
    onclick={handleClick}
    onkeydown={handleKeydown}
    class="cursor-pointer transition-all duration-200 hover:scale-105"
  >
    <Badge variant={badgeVariant} size="sm">
      <span class="flex items-center gap-1">
        {count}
        {#if isClickable}
          <Icon icon="lucide:arrow-right" class="h-3 w-3" />
        {/if}
      </span>
    </Badge>
  </button>
{:else}
  <Badge variant="neutral" size="sm">0</Badge>
{/if}
