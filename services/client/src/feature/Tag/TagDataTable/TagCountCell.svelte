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

  const badgeVariant = type === 'images' ? 'blue' : 'emerald';
  const isClickable = type === 'images' && count > 0 && tag;

  const handleClick = () => {
    if (isClickable) {
      const historyUrl = tagFilterUtils.buildHistoryUrl(tag);
      goto(historyUrl);
    }
  };

  const handleKeydown = (event: KeyboardEvent) => {
    if ((event.key === 'Enter' || event.key === ' ') && isClickable) {
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
      class="cursor-pointer transition-all duration-200 hover:scale-105"
    >
      <Badge variant={badgeVariant} size="sm">
        <span class="flex items-center gap-1">
          {count}
          <Icon icon="lucide:arrow-right" class="h-3 w-3" />
        </span>
      </Badge>
    </button>
  {:else}
    <Badge variant={badgeVariant} size="sm">
      {count}
    </Badge>
  {/if}
{:else}
  <Badge variant="neutral" size="sm">0</Badge>
{/if}
