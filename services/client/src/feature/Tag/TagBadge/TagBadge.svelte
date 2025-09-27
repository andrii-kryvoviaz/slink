<script lang="ts">
  import { TagDepthDots } from '@slink/feature/Tag';
  import Badge from '@slink/feature/Text/Badge/Badge.svelte';
  import type { BadgeProps } from '@slink/feature/Text/Badge/Badge.types';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import {
    getTagLastSegment,
    getTagParentPath,
    isTagNested,
  } from '@slink/lib/utils/tag';

  type DotsVariant = {
    showFullPath?: false;
    maxDotsToShow?: number;
  };

  type FullPathVariant = {
    showFullPath: true;
    maxDotsToShow?: never;
  };

  interface BaseProps extends BadgeProps {
    tag: Tag;
    class?: string;
    showCount?: boolean;
    onClose?: () => void;
  }

  type Props = BaseProps & (DotsVariant | FullPathVariant);

  let {
    tag,
    variant = 'neon',
    size = 'sm',
    outline = false,
    class: className = '',
    maxDotsToShow = 5,
    showCount = true,
    showFullPath,
    onClose,
  }: Props = $props();

  const tagName = getTagLastSegment(tag);
  const isNested = isTagNested(tag);
  const parentPath = getTagParentPath(tag);
  const fullPathLabel = isNested ? `${parentPath} / ${tagName}` : tag.name;
</script>

<Badge {variant} {size} {outline} class="shrink-0 {className}">
  <div class="group flex items-center gap-1.5" title={fullPathLabel}>
    <Icon icon="ph:tag" class="h-3 w-3" />

    {#if isNested && showFullPath}
      <div class="flex items-center gap-1">
        <span class="opacity-60">{parentPath}</span>
        <Icon icon="ph:caret-right" class="h-2.5 w-2.5" />
        <span class="font-medium">{tagName}</span>
      </div>
    {:else if isNested && !showFullPath}
      <div class="flex items-center gap-1">
        <div class="flex items-center gap-1 group-hover:hidden">
          <TagDepthDots {tag} {maxDotsToShow} {showCount} />
          <span class="font-medium">{tagName}</span>
        </div>

        <div class="hidden items-center gap-1 group-hover:flex">
          <span class="opacity-60">{parentPath}</span>
          <Icon icon="ph:caret-right" class="h-2.5 w-2.5" />
          <span class="font-medium">{tagName}</span>
        </div>
      </div>
    {:else}
      <span class="font-medium">{tag.name}</span>
    {/if}

    {#if showCount && tag.imageCount > 0}
      <span
        class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-blue-500/20 text-blue-600 dark:bg-blue-400/20 dark:text-blue-400"
      >
        {tag.imageCount}
      </span>
    {/if}

    {#if onClose}
      <button
        onclick={onClose}
        class="h-4 w-4 p-0 rounded-full ml-1 transition-colors hover:bg-slate-500/20 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300 dark:hover:bg-slate-400/20 flex items-center justify-center opacity-60 hover:opacity-100"
        aria-label="Remove {tag.name} tag"
      >
        <Icon icon="ph:x" class="h-2.5 w-2.5" />
      </button>
    {/if}
  </div>
</Badge>
