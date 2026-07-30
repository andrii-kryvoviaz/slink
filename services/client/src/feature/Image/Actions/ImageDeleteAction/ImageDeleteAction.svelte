<script lang="ts">
  import { ImageDeletePopover } from '@slink/feature/Image';

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
  icon="lucide:trash-2"
  label="Delete image"
  overlayKey="delete"
  variant="destructive"
  disabled={actions.deleteIsLoading}
>
  <ImageDeletePopover
    loading={actions.deleteIsLoading}
    close={() => (actions.overlays.delete = false)}
    confirm={({ preserveOnDiskAfterDeletion }) =>
      actions.handleDelete(preserveOnDiskAfterDeletion)}
  />
</OverlayAction>
