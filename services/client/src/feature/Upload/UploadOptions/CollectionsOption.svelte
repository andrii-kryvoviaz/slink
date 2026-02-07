<script lang="ts">
  import {
    CollectionListView,
    CreateCollectionDialog,
  } from '@slink/feature/Collection';
  import { AddButton, SelectionPill } from '@slink/ui/components/pill';
  import * as Popover from '@slink/ui/components/popover';

  import type { CollectionResponse } from '@slink/api/Response';

  import { createCollectionSelectionState } from '@slink/lib/state/CollectionSelectionState.svelte';
  import { createCreateCollectionModalState } from '@slink/lib/state/CreateCollectionModalState.svelte';

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
    createModalState.open((collection) => {
      selectionState.addCollection(collection);
      onCollectionsChange?.([...selectedCollections, collection]);
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
        label={selectedCollections.length > 0 ? 'Add more' : 'Collection'}
        icon="ph:folder-simple"
        variant="indigo"
        {disabled}
      />
    {/snippet}
  </Popover.Trigger>
  <Popover.Content
    class="p-0 bg-white/95 dark:bg-slate-900/95 border border-slate-200/70 dark:border-slate-700/50 rounded-xl shadow-xl shadow-black/10 dark:shadow-black/30 overflow-hidden backdrop-blur-sm"
    sideOffset={8}
    align="start"
    onpaste={(e: ClipboardEvent) => e.stopPropagation()}
  >
    <CollectionListView
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
