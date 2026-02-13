<script lang="ts">
  import {
    type TagDropdownContentVariants,
    TagListItem,
    tagDropdownDividerVariants,
  } from '@slink/feature/Tag';
  import { Button } from '@slink/ui/components/button';
  import { PickerEmptyState } from '@slink/ui/components/picker';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { getTagDisplayName } from '@slink/utils/tag';

  interface Props extends TagDropdownContentVariants {
    isOpen: boolean;
    tags: Tag[];
    searchTerm: string;
    creatingChildFor: Tag | null;
    childTagName: string;
    isCreating: boolean;
    isLoading?: boolean;
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
    isLoading = false,
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
  <div class="max-h-60 overflow-y-auto py-1.5">
    {#if canCreate && searchTerm.trim() && !creatingChildFor}
      <div class="flex px-1.5">
        <Button
          variant="ghost"
          size="sm"
          rounded="lg"
          class="flex-1 justify-start {highlightedIndex === 0
            ? 'bg-blue-50 dark:bg-blue-500/20 text-blue-600 dark:text-blue-300'
            : ''}"
          loading={isCreating}
          disabled={isCreating}
          onclick={onCreateTag}
        >
          <Icon icon="ph:plus" class="w-4 h-4" />
          <span class="truncate">Create "{searchTerm}"</span>
        </Button>
      </div>
    {/if}

    {#if creatingChildFor && childTagName.trim()}
      <div class="flex px-1.5">
        <Button
          variant="ghost"
          size="sm"
          rounded="lg"
          class="flex-1 justify-start"
          loading={isCreating}
          disabled={isCreating}
          onclick={onCreateTag}
        >
          <Icon icon="ph:plus" class="w-4 h-4" />
          <span class="truncate"
            >Create "{getTagDisplayName(creatingChildFor)} › {childTagName}"</span
          >
        </Button>
      </div>
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
      {#if isLoading}
        <div class="flex items-center justify-center py-4">
          <Icon
            icon="ph:spinner"
            class="w-4 h-4 animate-spin text-muted-foreground"
          />
        </div>
      {:else}
        <PickerEmptyState
          onAction={allowCreate && canCreate ? onCreateTag : undefined}
        >
          {#snippet icon()}
            <Icon
              icon="ph:magnifying-glass"
              class="w-5 h-5 text-gray-400 dark:text-gray-500"
            />
          {/snippet}
          {#snippet message()}
            No tags matching "{searchTerm}"
          {/snippet}
          {#snippet action()}
            Create "{searchTerm}"
          {/snippet}
        </PickerEmptyState>
      {/if}
    {:else if !creatingChildFor && tags.length === 0 && !canCreate && !searchTerm.trim()}
      {#if isLoading}
        <div class="flex items-center justify-center py-4">
          <Icon
            icon="ph:spinner"
            class="w-4 h-4 animate-spin text-muted-foreground"
          />
        </div>
      {:else}
        <PickerEmptyState>
          {#snippet icon()}
            <Icon
              icon="ph:tag"
              class="w-5 h-5 text-gray-400 dark:text-gray-500"
            />
          {/snippet}
          {#snippet message()}
            No tags yet
          {/snippet}
        </PickerEmptyState>
      {/if}
    {/if}
  </div>
{/if}
