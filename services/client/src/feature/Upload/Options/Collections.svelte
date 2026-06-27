<script lang="ts">
  import {
    CollectionPickerList,
    CreateCollectionDialog,
  } from '@slink/feature/Collection';
  import { Button } from '@slink/ui/components/button';
  import { SelectionPill } from '@slink/ui/components/pill';
  import * as Popover from '@slink/ui/components/popover';

  import Icon from '@iconify/svelte';

  import type { CollectionResponse } from '@slink/api/Response';

  import { UploadOptionsPopoverTheme } from './Options.theme';
  import { createUploadCollectionPicker } from './UploadPickerState.svelte';

  interface Props {
    selectedCollections?: CollectionResponse[];
    onCollectionsChange?: (collections: CollectionResponse[]) => void;
    disabled?: boolean;
  }

  let {
    selectedCollections = [],
    onCollectionsChange,
    disabled = false,
  }: Props = $props();

  const picker = createUploadCollectionPicker({
    selected: () => selectedCollections,
    onChange: (collections) => onCollectionsChange?.(collections),
  });

  const selectedIds = $derived(selectedCollections.map((c) => c.id));
  const buttonLabel = $derived(
    selectedCollections.length > 0 ? 'Add more' : 'Collections',
  );
</script>

<Popover.Root open={picker.open} onOpenChange={picker.setOpen}>
  <Popover.Trigger {disabled}>
    {#snippet child({ props })}
      <Button
        {...props}
        variant="soft-indigo"
        rounded="full"
        size="sm"
        {disabled}
      >
        <Icon icon="ph:folder-simple" class="w-3.5 h-3.5" />
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
    <CollectionPickerList
      collections={picker.catalog.items}
      {selectedIds}
      isLoading={picker.catalog.isLoading}
      {disabled}
      variant="glass"
      onToggle={picker.toggle}
      create={picker.create}
    />
  </Popover.Content>
</Popover.Root>

{#each selectedCollections as collection (collection.id)}
  <SelectionPill
    label={collection.name}
    icon="ph:folder-simple-fill"
    variant="indigo"
    {disabled}
    onRemove={() => picker.detach(collection.id)}
  />
{/each}

<CreateCollectionDialog modalState={picker.modal} />
