<script lang="ts">
  import { fade } from 'svelte/transition';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { TagCreationButton } from '../TagCreationButton';
  import { TagListItem } from '../TagListItem';
  import {
    type TagDropdownContentVariants,
    tagDropdownContentVariants,
    tagDropdownDividerVariants,
    tagDropdownEmptyStateVariants,
  } from './TagDropdownContent.theme';

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
    variant?: 'default' | 'neon' | 'minimal';
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

  const shouldShowContent = $derived(
    tags.length > 0 || canCreate || creatingChildFor,
  );
</script>

{#if isOpen && shouldShowContent}
  <div
    id="tag-dropdown"
    class={tagDropdownContentVariants({ variant })}
    in:fade={{ delay: 50, duration: 200 }}
    out:fade={{ duration: 150 }}
  >
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
          {@const itemIndex =
            canCreate && searchTerm.trim() ? index + 1 : index}
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

      {#if !creatingChildFor && tags.length === 0 && !canCreate && searchTerm.trim()}
        <div class={tagDropdownEmptyStateVariants({ variant })}>
          <div class="text-sm text-muted-foreground">
            No tags found for "{searchTerm}"
          </div>
        </div>
      {/if}
    </div>
  </div>
{/if}
