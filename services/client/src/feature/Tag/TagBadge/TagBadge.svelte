<script lang="ts">
  import { TagDepthDots } from '@slink/feature/Tag';
  import Badge from '@slink/feature/Text/Badge/Badge.svelte';
  import type { BadgeProps } from '@slink/feature/Text/Badge/Badge.types';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { getTagLastSegment, isTagNested } from '@slink/lib/utils/tag';

  interface Props extends BadgeProps {
    tag: Tag;
    class?: string;
    maxDotsToShow?: number;
    showCount?: boolean;
  }

  let {
    tag,
    variant = 'neon',
    size = 'sm',
    outline = false,
    class: className = '',
    maxDotsToShow = 5,
    showCount = true,
  }: Props = $props();

  const tagName = getTagLastSegment(tag);
  const isNested = isTagNested(tag);
</script>

<Badge {variant} {size} {outline} class="shrink-0 {className}">
  <div class="flex items-center gap-1">
    {#if isNested}
      <TagDepthDots {tag} {maxDotsToShow} {showCount} />
    {/if}
    <Icon icon="lucide:tag" class="h-3 w-3" />
    <span class="font-medium">{tagName}</span>
  </div>
</Badge>
