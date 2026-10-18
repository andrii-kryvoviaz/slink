<script lang="ts">
  import { TagPickerList } from '@slink/feature/Tag';
  import {
    ImageItemPicker,
    type PickerVariant,
  } from '@slink/ui/components/picker';
  import type { Snippet } from 'svelte';

  import type { CreateTagModalState } from '@slink/lib/state/CreateTagModalState.svelte';
  import type { TagImagePickerState } from '@slink/lib/state/ImagePickerState.svelte';

  interface Props {
    pickerState: TagImagePickerState;
    createModalState: CreateTagModalState;
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
    <TagPickerList
      tags={items}
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
