<script lang="ts">
  import { TagSelector } from '@slink/feature/Tag';
  import { AddButton, SelectionPill } from '@slink/ui/components/pill';
  import * as Popover from '@slink/ui/components/popover';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { getTagLastSegment } from '@slink/lib/utils/tag';

  interface Props {
    selectedTags?: Tag[];
    onTagsChange?: (tags: Tag[]) => void;
    disabled?: boolean;
  }

  let { selectedTags = [], onTagsChange, disabled = false }: Props = $props();
  let isOpen = $state(false);
  let tagSelectorRef = $state<{ focusInput: () => void }>();

  const handleRemoveTag = (tagId: string) => {
    if (disabled) return;
    onTagsChange?.(selectedTags.filter((t) => t.id !== tagId));
  };

  $effect(() => {
    if (isOpen) {
      setTimeout(() => tagSelectorRef?.focusInput(), 50);
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
    class="w-72 p-0 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg"
    sideOffset={8}
    align="start"
  >
    <div class="p-3">
      <TagSelector
        bind:this={tagSelectorRef}
        {selectedTags}
        {onTagsChange}
        {disabled}
        placeholder="Search or create tags..."
        variant="minimal"
        allowCreate={true}
        hideIcon={true}
      />
    </div>
  </Popover.Content>
</Popover.Root>

{#each selectedTags as tag (tag.id)}
  <SelectionPill
    label={getTagLastSegment(tag)}
    icon="ph:tag-fill"
    variant="blue"
    {disabled}
    onRemove={() => handleRemoveTag(tag.id)}
  />
{/each}
