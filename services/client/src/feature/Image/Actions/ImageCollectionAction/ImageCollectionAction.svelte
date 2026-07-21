<script lang="ts">
  import { CollectionPicker } from '@slink/feature/Collection';
  import { ButtonGroupItem, DropdownSimpleItem } from '@slink/ui/components';
  import { Overlay } from '@slink/ui/components/popover';

  import Icon from '@iconify/svelte';

  import { actionButtonVariants, iconSizeVariants } from '../actions.theme';
  import { getImageActionsContext } from '../context';

  interface Props {
    display?: 'button' | 'item';
  }

  let { display = 'button' }: Props = $props();

  const context = getImageActionsContext();
  const { actions } = context;

  const iconClass = $derived(iconSizeVariants({ layout: context.layout }));

  const openFromMenu = () => {
    actions.overlays.collection = true;
  };
</script>

{#if display === 'item'}
  <DropdownSimpleItem on={{ click: openFromMenu }}>
    {#snippet icon()}
      <Icon icon="lucide:folder" class="h-4 w-4" />
    {/snippet}
    <span>Add to collection</span>
  </DropdownSimpleItem>
{:else}
  <Overlay
    bind:open={actions.overlays.collection}
    variant="floating"
    size="none"
    contentProps={context.overlayContentProps}
  >
    {#snippet trigger()}
      <ButtonGroupItem
        variant="default"
        size="md"
        class={actionButtonVariants({ layout: context.layout })}
        aria-label="Add to collection"
        tooltip="Add to collection"
        disableTooltip={actions.overlays.collection}
      >
        <Icon icon="lucide:folder" class={iconClass} />
      </ButtonGroupItem>
    {/snippet}
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
  </Overlay>
{/if}
