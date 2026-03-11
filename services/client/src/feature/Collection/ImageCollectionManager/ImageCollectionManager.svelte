<script lang="ts">
  import {
    CollectionPicker,
    CreateCollectionDialog,
  } from '@slink/feature/Collection';

  import { createCreateCollectionModalState } from '@slink/lib/state/CreateCollectionModalState.svelte';
  import { createCollectionPickerState } from '@slink/lib/state/ImagePickerState.svelte';

  interface Props {
    imageId: string;
  }

  let { imageId }: Props = $props();

  const pickerState = createCollectionPickerState();
  const createModalState = createCreateCollectionModalState();

  $effect(() => {
    pickerState.setImage(imageId);
    pickerState.load();
  });
</script>

<CollectionPicker {pickerState} {createModalState} variant="panel" />
<CreateCollectionDialog modalState={createModalState} />
