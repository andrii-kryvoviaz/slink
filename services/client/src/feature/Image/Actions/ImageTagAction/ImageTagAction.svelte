<script lang="ts">
  import { TagPicker } from '@slink/feature/Tag';

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
  icon="lucide:tag"
  label="Manage tags"
  overlayKey="tag"
  size="none"
>
  <TagPicker
    pickerState={actions.tagPickerState}
    createModalState={actions.createTagModalState}
    variant="popover"
    onClose={() => (actions.overlays.tag = false)}
    onToggle={actions.handleTagToggle}
    onBeforeCreate={actions.overlays.suspend}
    onAfterClose={actions.overlays.restore}
  >
    {#snippet title()}Manage tags{/snippet}
  </TagPicker>
</OverlayAction>
