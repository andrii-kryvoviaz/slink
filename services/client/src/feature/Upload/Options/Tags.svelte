<script lang="ts">
  import { CreateTagDialog, TagPickerList } from '@slink/feature/Tag';
  import { Button } from '@slink/ui/components/button';
  import { SelectionPill } from '@slink/ui/components/pill';
  import * as Popover from '@slink/ui/components/popover';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';

  import { getTagLastSegment } from '@slink/lib/utils/tag';

  import { UploadOptionsPopoverTheme } from './Options.theme';
  import { createUploadTagPicker } from './UploadPickerState.svelte';

  interface Props {
    selectedTags?: Tag[];
    onTagsChange?: (tags: Tag[]) => void;
    disabled?: boolean;
  }

  let { selectedTags = [], onTagsChange, disabled = false }: Props = $props();

  const picker = createUploadTagPicker({
    selected: () => selectedTags,
    onChange: (tags) => onTagsChange?.(tags),
  });

  const selectedIds = $derived(selectedTags.map((t) => t.id));
  const buttonLabel = $derived(selectedTags.length > 0 ? 'Add more' : 'Tags');
</script>

<Popover.Root open={picker.open} onOpenChange={picker.setOpen}>
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
    class={UploadOptionsPopoverTheme()}
    sideOffset={8}
    align="start"
    onpaste={(e: ClipboardEvent) => e.stopPropagation()}
  >
    <TagPickerList
      tags={picker.catalog.items}
      {selectedIds}
      isLoading={picker.catalog.isLoading}
      {disabled}
      variant="glass"
      onToggle={picker.toggle}
      create={picker.create}
    />
  </Popover.Content>
</Popover.Root>

{#each selectedTags as tag (tag.id)}
  <SelectionPill
    label={getTagLastSegment(tag)}
    icon="ph:tag-fill"
    variant="blue"
    {disabled}
    onRemove={() => picker.detach(tag.id)}
  />
{/each}

<CreateTagDialog modalState={picker.modal} />
