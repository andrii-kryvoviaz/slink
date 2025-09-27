<script lang="ts">
  import type { Tag } from '@slink/api/Resources/TagResource';

  import { getTagPathSegments } from '@slink/lib/utils/tag';

  interface Props {
    tag: Tag;
    maxDotsToShow?: number;
    showCount?: boolean;
  }

  let { tag, maxDotsToShow = 5, showCount = true }: Props = $props();

  const pathSegments = getTagPathSegments(tag);
  const depth = pathSegments.length - 1;
</script>

{#if depth > 0}
  <div class="flex items-center gap-0.5">
    {#if depth > maxDotsToShow && showCount}
      <span class="text-xs font-bold opacity-80">{depth}</span>
    {:else}
      {#each Array(Math.min(depth, maxDotsToShow)) as _}
        <div class="w-1 h-1 bg-current rounded-full opacity-60"></div>
      {/each}
    {/if}
  </div>
{/if}
