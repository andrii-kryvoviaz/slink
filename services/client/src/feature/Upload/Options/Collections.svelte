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

  import { createCollectionSelectionState } from '@slink/lib/state/CollectionSelectionState.svelte';
  import { createCreateCollectionModalState } from '@slink/lib/state/CreateCollectionModalState.svelte';

  import { UploadOptionsPopoverTheme } from './Options.theme';

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

  let isOpen = $state(false);

  const selectionState = createCollectionSelectionState();
  const createModalState = createCreateCollectionModalState();

  const selectedIds = $derived(selectedCollections.map((c) => c.id));
  const buttonLabel = $derived(
    selectedCollections.length > 0 ? 'Add more' : 'Collections',
  );

  const handleToggle = (collection: CollectionResponse) => {
    if (disabled) return;

    const isSelected = selectedIds.includes(collection.id);
    const newSelections = isSelected
      ? selectedCollections.filter((c) => c.id !== collection.id)
      : [...selectedCollections, collection];

    onCollectionsChange?.(newSelections);
  };

  const handleRemove = (collectionId: string) => {
    if (disabled) return;
    onCollectionsChange?.(
      selectedCollections.filter((c) => c.id !== collectionId),
    );
  };

  const handleCreateNew = () => {
    isOpen = false;
    createModalState.open(
      (collection) => {
        selectionState.addCollection(collection);
        onCollectionsChange?.([...selectedCollections, collection]);
      },
      () => {
        isOpen = true;
      },
    );
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
      collections={selectionState.collections}
      {selectedIds}
      isLoading={selectionState.isLoading}
      {disabled}
      variant="glass"
      onToggle={handleToggle}
      onCreateNew={handleCreateNew}
    />
  </Popover.Content>
</Popover.Root>

{#each selectedCollections as collection (collection.id)}
  <SelectionPill
    label={collection.name}
    icon="ph:folder-simple-fill"
    variant="indigo"
    {disabled}
    onRemove={() => handleRemove(collection.id)}
  />
{/each}

<CreateCollectionDialog modalState={createModalState} />
