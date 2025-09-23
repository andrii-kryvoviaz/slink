<script lang="ts">
  import { Button } from '@slink/ui/components/button';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import {
    getTagLastSegment,
    getTagParentPath,
    isTagNested,
  } from '@slink/utils/tag';
  import { cn } from '@slink/utils/ui';

  import {
    type TagPillVariants,
    tagPillBadgeVariants,
    tagPillIconVariants,
    tagPillRemoveButtonVariants,
    tagPillTextVariants,
    tagPillVariants,
  } from './TagPill.theme';

  interface Props extends TagPillVariants {
    tag: Tag;
    removable?: boolean;
    onRemove?: () => void;
  }

  let {
    tag,
    removable = false,
    onRemove,
    variant = 'neon',
    size = 'sm',
  }: Props = $props();

  const isNested = $derived(isTagNested(tag));
  const parentPath = $derived(getTagParentPath(tag));
  const lastSegment = $derived(getTagLastSegment(tag));
</script>

<div class={tagPillVariants({ variant, size, nested: isNested })}>
  <Icon icon="ph:tag" class={tagPillIconVariants({ variant })} />

  {#if isNested}
    <div class="flex items-center gap-1">
      <span class={tagPillTextVariants({ variant, type: 'secondary' })}>
        {parentPath}
      </span>
      <Icon
        icon="ph:caret-right"
        class={cn('h-2.5 w-2.5', tagPillIconVariants({ variant }))}
      />
      <span class={tagPillTextVariants({ variant })}>
        {lastSegment}
      </span>
    </div>
  {:else}
    <span class={tagPillTextVariants({ variant })}>
      {tag.name}
    </span>
  {/if}

  {#if tag.imageCount > 0}
    <span class={tagPillBadgeVariants({ variant })}>
      {tag.imageCount}
    </span>
  {/if}

  {#if removable}
    <Button
      size="sm"
      variant="ghost"
      onclick={onRemove}
      aria-label="Remove {tag.name} tag"
      class={tagPillRemoveButtonVariants({ variant })}
    >
      <Icon icon="ph:x" class="h-2.5 w-2.5" />
    </Button>
  {/if}
</div>
