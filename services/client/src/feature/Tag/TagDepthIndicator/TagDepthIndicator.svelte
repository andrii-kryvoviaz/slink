<script lang="ts">
  import Badge from '@slink/feature/Text/Badge/Badge.svelte';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import {
    getTagLastSegment,
    getTagPathSegments,
    isTagNested,
  } from '@slink/lib/utils/tag';

  interface Props {
    tag: Tag;
    showFullPath?: boolean;
    maxDotsToShow?: number;
  }

  let { tag, showFullPath = false, maxDotsToShow = 5 }: Props = $props();

  const pathSegments = getTagPathSegments(tag);
  const tagName = getTagLastSegment(tag);
  const isNested = isTagNested(tag);
  const depth = pathSegments.length - 1;
</script>

<div class="flex items-center gap-2 min-w-0">
  {#if isNested}
    <Badge variant="neutral" size="xs" class="shrink-0">
      <div class="flex items-center gap-1">
        <Icon icon="lucide:layers" class="h-3 w-3" />
        {#if depth > 0}
          <div class="flex items-center gap-0.5">
            {#if depth > maxDotsToShow}
              <span class="text-xs font-bold opacity-80">{depth}</span>
            {:else}
              {#each Array(Math.min(depth, maxDotsToShow)) as _}
                <div class="w-1 h-1 bg-current rounded-full opacity-60"></div>
              {/each}
            {/if}
          </div>
        {/if}
      </div>
    </Badge>
  {/if}

  {#if showFullPath && isNested}
    <div class="flex items-center gap-1 min-w-0">
      {#each pathSegments as segment, index}
        {#if index < depth}
          <span class="text-xs text-muted-foreground opacity-70">{segment}</span
          >
          <Icon
            icon="lucide:chevron-right"
            class="h-3 w-3 text-muted-foreground shrink-0"
          />
        {:else}
          <span class="text-sm font-medium truncate">{segment}</span>
        {/if}
      {/each}
    </div>
  {:else}
    <span class="text-sm font-medium truncate">{tagName}</span>
  {/if}
</div>
