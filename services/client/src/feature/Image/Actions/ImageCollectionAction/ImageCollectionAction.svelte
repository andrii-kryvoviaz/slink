<script lang="ts">
  import { CollectionPicker } from '@slink/feature/Collection';

  import OverlayAction from '../OverlayAction/OverlayAction.svelte';
  import { getImageActionsContext } from '../context';

  interface Props {
    display?: 'button' | 'item';
  }

  let { display = 'button' }: Props = $props();

  const { actions } = getImageActionsContext();
</script>

<OverlayAction
  {display}
  icon="lucide:folder"
  label="Add to collection"
  overlayKey="collection"
  size="none"
>
  <CollectionPicker
    pickerState={actions.collectionPickerState}
    createModalState={actions.createCollectionModalState}
    variant="popover"
    onClose={() => (actions.overlays.collection = false)}
    onToggle={actions.handleCollectionToggle}
    onBeforeCreate={actions.overlays.suspend}
    onAfterClose={actions.overlays.restore}
  >
    {#snippet title()}Add to collection{/snippet}
  </CollectionPicker>
</OverlayAction>
