<script lang="ts">
  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { getTagDisplayName } from '@slink/utils/tag';

  import {
    type TagListItemVariants,
    tagListActionButtonVariants,
    tagListBadgeVariants,
    tagListIconVariants,
    tagListItemVariants,
  } from './TagListItem.theme';

  interface Props extends TagListItemVariants {
    tag: Tag;
    onSelect: (tag: Tag) => void;
    onAddChild: (tag: Tag) => void;
    allowCreate?: boolean;
    highlighted?: boolean;
  }

  let {
    tag,
    onSelect,
    onAddChild,
    variant = 'default',
    allowCreate = true,
    highlighted = false,
  }: Props = $props();

  const handleAddChild = (e: MouseEvent) => {
    e.stopPropagation();
    onAddChild(tag);
  };
</script>

<div class="flex items-center mx-1 my-0.5">
  <button
    class={`group ${tagListItemVariants({ variant, highlighted })}`}
    onclick={() => onSelect(tag)}
  >
    <Icon icon="ph:hash" class={tagListIconVariants({ variant })} />
    <span class="flex-1 truncate">{getTagDisplayName(tag)}</span>
    {#if tag.imageCount > 0}
      <span class={tagListBadgeVariants({ variant })}>
        {tag.imageCount}
      </span>
    {/if}
  </button>
  {#if allowCreate}
    <button
      class={tagListActionButtonVariants({ variant })}
      onclick={handleAddChild}
      aria-label={`Add child to ${tag.name}`}
    >
      <Icon icon="ph:plus" class="h-4 w-4" />
    </button>
  {/if}
</div>
