<script lang="ts">
  import { CreateTagDialog, TagListView } from '@slink/feature/Tag';
  import { Button } from '@slink/ui/components/button';
  import { SelectionPill } from '@slink/ui/components/pill';
  import * as Popover from '@slink/ui/components/popover';

  import Icon from '@iconify/svelte';

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
  const buttonLabel = $derived(
    selectedTags.length > 0 ? 'Add more' : 'Add tags',
  );

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
    isOpen = false;
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
      <Button
        {...props}
        variant="soft-blue"
        rounded="full"
        size="sm"
        {disabled}
      >
        <Icon icon="ph:tag" class="w-3.5 h-3.5" />
        {buttonLabel}
        <Icon icon="ph:plus" class="w-3 h-3 opacity-60" />
      </Button>
    {/snippet}
  </Popover.Trigger>
  <Popover.Content
    class="p-0 bg-white/95 dark:bg-slate-900/95 border border-slate-200/50 dark:border-slate-700/40 rounded-xl shadow-lg shadow-black/8 dark:shadow-black/25 overflow-hidden backdrop-blur-sm"
    sideOffset={8}
    align="start"
    onpaste={(e: ClipboardEvent) => e.stopPropagation()}
  >
    <TagListView
      tags={selectionState.tags}
      {selectedIds}
      isLoading={selectionState.isLoading}
      {disabled}
      variant="glass"
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
