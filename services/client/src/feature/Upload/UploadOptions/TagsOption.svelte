<script lang="ts">
  import { CreateTagDialog, TagListView } from '@slink/feature/Tag';
  import { AddButton, SelectionPill } from '@slink/ui/components/pill';
  import * as Popover from '@slink/ui/components/popover';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { createCreateTagModalState } from '@slink/lib/state/CreateTagModalState.svelte';
  import { createTagSelectionState } from '@slink/lib/state/TagSelectionState.svelte';
  import { getTagLastSegment } from '@slink/lib/utils/tag';

  interface Props {
    selectedTags?: Tag[];
    onTagsChange?: (tags: Tag[]) => void;
    disabled?: boolean;
  }

  let { selectedTags = [], onTagsChange, disabled = false }: Props = $props();
  let isOpen = $state(false);

  const selectionState = createTagSelectionState();
  const createModalState = createCreateTagModalState();

  const selectedIds = $derived(selectedTags.map((t) => t.id));

  const handleToggle = (tag: Tag) => {
    if (disabled) return;

    const isSelected = selectedIds.includes(tag.id);
    const newSelections = isSelected
      ? selectedTags.filter((t) => t.id !== tag.id)
      : [...selectedTags, tag];

    onTagsChange?.(newSelections);
  };

  const handleRemove = (tagId: string) => {
    if (disabled) return;
    onTagsChange?.(selectedTags.filter((t) => t.id !== tagId));
  };

  const handleCreateNew = () => {
    createModalState.open((tag) => {
      selectionState.addTag(tag);
      onTagsChange?.([...selectedTags, tag]);
    });
  };

  $effect(() => {
    if (isOpen) {
      selectionState.load();
    }
  });
</script>

<Popover.Root bind:open={isOpen}>
  <Popover.Trigger {disabled}>
    {#snippet child({ props })}
      <AddButton
        {...props}
        label={selectedTags.length > 0 ? 'Add more' : 'Add tags'}
        icon="ph:tag"
        variant="blue"
        {disabled}
      />
    {/snippet}
  </Popover.Trigger>
  <Popover.Content
    class="p-0 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg overflow-hidden"
    sideOffset={8}
    align="start"
  >
    <TagListView
      tags={selectionState.tags}
      {selectedIds}
      isLoading={selectionState.isLoading}
      {disabled}
      variant="popover"
      onToggle={handleToggle}
      onCreateNew={handleCreateNew}
    />
  </Popover.Content>
</Popover.Root>

{#each selectedTags as tag (tag.id)}
  <SelectionPill
    label={getTagLastSegment(tag)}
    icon="ph:tag-fill"
    variant="blue"
    {disabled}
    onRemove={() => handleRemove(tag.id)}
  />
{/each}

<CreateTagDialog modalState={createModalState} />
