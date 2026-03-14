<script lang="ts">
  import { CollectionPickerList } from '@slink/feature/Collection';
  import {
    ImageItemPicker,
    type PickerVariant,
  } from '@slink/ui/components/picker';

  import type { CreateCollectionModalState } from '@slink/lib/state/CreateCollectionModalState.svelte';
  import type { CollectionImagePickerState } from '@slink/lib/state/ImagePickerState.svelte';

  interface Props {
    pickerState: CollectionImagePickerState;
    createModalState: CreateCollectionModalState;
    variant?: PickerVariant;
    onToggle?: (result: { added: boolean; itemId: string }) => void;
    onBeforeCreate?: () => void;
    onAfterClose?: () => void;
  }

  let {
    pickerState,
    createModalState,
    variant = 'popover',
    onToggle,
    onBeforeCreate,
    onAfterClose,
  }: Props = $props();
</script>

<ImageItemPicker
  {pickerState}
  {createModalState}
  {variant}
  {onToggle}
  {onBeforeCreate}
  {onAfterClose}
>
  {#snippet listView({
    items,
    selectedIds,
    isLoading,
    togglingId,
    variant: v,
    onToggle: toggle,
    onCreateNew,
  })}
    <CollectionPickerList
      collections={items}
      {selectedIds}
      {isLoading}
      {togglingId}
      variant={v}
      onToggle={toggle}
      {onCreateNew}
    />
  {/snippet}
</ImageItemPicker>
