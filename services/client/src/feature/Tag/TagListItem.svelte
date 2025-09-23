<script lang="ts">
  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { cn } from '@slink/utils/ui';

  interface Props {
    tag: Tag;
    onSelect: (tag: Tag) => void;
    onAddChild: (tag: Tag) => void;
  }

  let { tag, onSelect, onAddChild }: Props = $props();

  const getTagDisplayName = (tag: Tag) => {
    if (tag.isRoot) {
      return tag.name;
    }
    return tag.path.replace('#', '').replace(/\//g, ' › ');
  };

  const handleAddChild = (e: MouseEvent) => {
    e.stopPropagation();
    onAddChild(tag);
  };
</script>

<div class="flex items-center">
  <button
    class={cn(
      'flex-1 flex items-center gap-2 px-3 py-2 text-sm text-left',
      'hover:bg-accent hover:text-accent-foreground',
      'focus:bg-accent focus:text-accent-foreground focus:outline-none',
    )}
    onclick={() => onSelect(tag)}
  >
    <Icon icon="ph:hash" class="h-4 w-4 text-muted-foreground" />
    <span class="flex-1 truncate">{getTagDisplayName(tag)}</span>
    {#if tag.imageCount > 0}
      <span
        class="text-xs text-muted-foreground bg-muted px-1.5 py-0.5 rounded"
      >
        {tag.imageCount}
      </span>
    {/if}
  </button>
  <button
    class={cn(
      'flex items-center justify-center w-8 h-8 mx-1',
      'text-muted-foreground hover:text-foreground hover:bg-accent rounded',
      'focus:outline-none focus:bg-accent focus:text-foreground',
      'transition-colors',
    )}
    onclick={handleAddChild}
    aria-label={`Add child to ${tag.name}`}
  >
    <Icon icon="ph:plus" class="h-3 w-3" />
  </button>
</div>
