<script lang="ts">
  import { TagDepthDots } from '@slink/feature/Tag';
  import Badge from '@slink/feature/Text/Badge/Badge.svelte';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { isTagNested } from '@slink/lib/utils/tag';

  interface Props {
    tag: Tag;
    maxDotsToShow?: number;
    showCount?: boolean;
  }

  let { tag, maxDotsToShow = 5, showCount = true }: Props = $props();

  const isNested = isTagNested(tag);
</script>

<div class="flex items-center gap-2 min-w-0">
  {#if isNested}
    <Badge variant="neutral" size="xs" class="shrink-0">
      <div class="flex items-center gap-1">
        <Icon icon="lucide:layers" class="h-3 w-3" />
        <TagDepthDots {tag} {maxDotsToShow} {showCount} />
      </div>
    </Badge>
  {/if}
</div>
