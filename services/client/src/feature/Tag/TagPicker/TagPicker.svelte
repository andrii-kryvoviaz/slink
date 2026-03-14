<script lang="ts">
  import { TagPickerList } from '@slink/feature/Tag';
  import {
    ImageItemPicker,
    type PickerVariant,
  } from '@slink/ui/components/picker';

  import type { CreateTagModalState } from '@slink/lib/state/CreateTagModalState.svelte';
  import type { TagImagePickerState } from '@slink/lib/state/ImagePickerState.svelte';

  interface Props {
    pickerState: TagImagePickerState;
    createModalState: CreateTagModalState;
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
    <TagPickerList
      tags={items}
      {selectedIds}
      {isLoading}
      {togglingId}
      variant={v}
      onToggle={toggle}
      {onCreateNew}
    />
  {/snippet}
</ImageItemPicker>
