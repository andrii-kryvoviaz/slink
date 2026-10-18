<script lang="ts">
  import { CollectionPickerList } from '@slink/feature/Collection';
  import {
    ImageItemPicker,
    type PickerVariant,
  } from '@slink/ui/components/picker';
  import type { Snippet } from 'svelte';

  import type { CreateCollectionModalState } from '@slink/lib/state/CreateCollectionModalState.svelte';
  import type { CollectionImagePickerState } from '@slink/lib/state/ImagePickerState.svelte';

  interface Props {
    pickerState: CollectionImagePickerState;
    createModalState: CreateCollectionModalState;
    variant?: PickerVariant;
    title?: Snippet;
    onClose?: () => void;
    onToggle?: (result: { added: boolean; itemId: string }) => void;
    onBeforeCreate?: () => void;
    onAfterClose?: () => void;
  }

  let {
    pickerState,
    createModalState,
    variant = 'popover',
    title,
    onClose,
    onToggle,
    onBeforeCreate,
    onAfterClose,
  }: Props = $props();
</script>

<ImageItemPicker
  {pickerState}
  {createModalState}
  {variant}
  {title}
  {onClose}
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
    onSearch,
  })}
    <CollectionPickerList
      collections={items}
      {selectedIds}
      {isLoading}
      {togglingId}
      variant={v}
      onToggle={toggle}
      {onSearch}
      create={{ detailed: onCreateNew }}
    />
  {/snippet}
</ImageItemPicker>
