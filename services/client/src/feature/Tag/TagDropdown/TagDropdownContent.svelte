<script lang="ts">
  import {
    TagCreationButton,
    type TagDropdownContentVariants,
    TagListItem,
    tagDropdownDividerVariants,
    tagDropdownEmptyStateVariants,
  } from '@slink/feature/Tag';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  interface Props extends TagDropdownContentVariants {
    isOpen: boolean;
    tags: Tag[];
    searchTerm: string;
    creatingChildFor: Tag | null;
    childTagName: string;
    isCreating: boolean;
    canCreate: boolean;
    allowCreate?: boolean;
    highlightedIndex?: number;
    onSelectTag: (tag: Tag) => void;
    onAddChild: (tag: Tag) => void;
    onCreateTag: () => void;
    variant?: 'default' | 'neon' | 'minimal' | 'subtle';
  }

  let {
    isOpen,
    tags,
    searchTerm,
    creatingChildFor,
    childTagName,
    isCreating,
    canCreate,
    allowCreate = true,
    highlightedIndex = -1,
    onSelectTag,
    onAddChild,
    onCreateTag,
    variant = 'default',
  }: Props = $props();
</script>

{#if isOpen}
  <div class="max-h-60 overflow-y-auto py-1">
    {#if canCreate && !creatingChildFor}
      <TagCreationButton
        {searchTerm}
        {creatingChildFor}
        {childTagName}
        {isCreating}
        {onCreateTag}
        {canCreate}
        {variant}
        highlighted={highlightedIndex === 0}
      />
    {/if}

    {#if creatingChildFor && childTagName.trim()}
      <TagCreationButton
        {searchTerm}
        {creatingChildFor}
        {childTagName}
        {isCreating}
        {onCreateTag}
        {canCreate}
        {variant}
        highlighted={false}
      />
    {/if}

    {#if ((creatingChildFor && childTagName.trim()) || (canCreate && searchTerm.trim() && !creatingChildFor)) && tags.length > 0}
      <div class={tagDropdownDividerVariants({ variant })}></div>
    {/if}

    {#if !creatingChildFor}
      {#each tags as tag, index (tag.id)}
        {@const itemIndex = canCreate && searchTerm.trim() ? index + 1 : index}
        <TagListItem
          {tag}
          onSelect={onSelectTag}
          {onAddChild}
          {variant}
          {allowCreate}
          highlighted={highlightedIndex === itemIndex}
        />
      {/each}
    {/if}

    {#if !creatingChildFor && tags.length === 0 && searchTerm.trim()}
      <div class={tagDropdownEmptyStateVariants({ variant })}>
        <div class="text-sm text-muted-foreground">
          No tags found for "{searchTerm}"
        </div>
        {#if allowCreate && canCreate}
          <button
            type="button"
            class="mt-2 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-500/10 dark:text-blue-400 dark:hover:bg-blue-500/20 transition-colors"
            onclick={onCreateTag}
          >
            <Icon icon="ph:plus" class="w-3 h-3" />
            Create "{searchTerm}"
          </button>
        {/if}
      </div>
    {:else if !creatingChildFor && tags.length === 0 && !canCreate && !searchTerm.trim()}
      <div class={tagDropdownEmptyStateVariants({ variant })}>
        <div class="text-sm text-muted-foreground">No tags yet</div>
      </div>
    {/if}
  </div>
{/if}
