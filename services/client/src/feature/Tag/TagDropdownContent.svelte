<script lang="ts">
  import { Loader } from '@slink/feature/Layout';

  import { fade } from 'svelte/transition';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { cn } from '@slink/utils/ui';

  import TagCreationButton from './TagCreationButton.svelte';
  import TagListItem from './TagListItem.svelte';

  interface Props {
    isOpen: boolean;
    isLoading: boolean;
    tags: Tag[];
    searchTerm: string;
    creatingChildFor: Tag | null;
    childTagName: string;
    isCreating: boolean;
    canCreate: boolean;
    onSelectTag: (tag: Tag) => void;
    onAddChild: (tag: Tag) => void;
    onCreateTag: () => void;
  }

  let {
    isOpen,
    isLoading,
    tags,
    searchTerm,
    creatingChildFor,
    childTagName,
    isCreating,
    canCreate,
    onSelectTag,
    onAddChild,
    onCreateTag,
  }: Props = $props();
</script>

{#if isOpen && (tags.length > 0 || canCreate || isLoading || creatingChildFor)}
  <div
    id="tag-dropdown"
    class={cn(
      'absolute top-full left-0 right-0 mt-1 z-50',
      'bg-popover border border-border rounded-md shadow-md',
    )}
  >
    {#if isLoading}
      <div
        class="flex items-center justify-center py-8"
        in:fade={{ delay: 200, duration: 200 }}
        out:fade={{ duration: 100 }}
      >
        <Loader variant="minimal" size="sm">
          <span class="text-sm text-muted-foreground">Loading tags...</span>
        </Loader>
      </div>
    {:else}
      <div class="max-h-60 overflow-y-auto py-1">
        <TagCreationButton
          {searchTerm}
          {creatingChildFor}
          {childTagName}
          {isCreating}
          {onCreateTag}
          {canCreate}
        />

        {#if ((creatingChildFor && childTagName.trim()) || (canCreate && searchTerm.trim() && !creatingChildFor)) && tags.length > 0}
          <div class="border-t border-border my-1"></div>
        {/if}

        {#if !creatingChildFor}
          {#each tags as tag (tag.id)}
            <TagListItem {tag} onSelect={onSelectTag} {onAddChild} />
          {/each}
        {/if}

        {#if !creatingChildFor && tags.length === 0 && !canCreate && searchTerm.trim()}
          <div class="px-3 py-6 text-center">
            <div class="text-sm text-muted-foreground">
              No tags found for "{searchTerm}"
            </div>
          </div>
        {/if}
      </div>
    {/if}
  </div>
{/if}
