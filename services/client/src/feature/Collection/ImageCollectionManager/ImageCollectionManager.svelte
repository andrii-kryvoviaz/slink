<script lang="ts">
  import { CollectionPicker } from '@slink/feature/Collection';

  import { createCollectionPickerState } from '@slink/lib/state/CollectionPickerState.svelte';
  import { createCreateCollectionModalState } from '@slink/lib/state/CreateCollectionModalState.svelte';

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
