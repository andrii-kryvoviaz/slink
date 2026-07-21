<script lang="ts">
  import { TagPicker } from '@slink/feature/Tag';
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
    actions.overlays.tag = true;
  };
</script>

{#if display === 'item'}
  <DropdownSimpleItem on={{ click: openFromMenu }}>
    {#snippet icon()}
      <Icon icon="lucide:tag" class="h-4 w-4" />
    {/snippet}
    <span>Manage tags</span>
  </DropdownSimpleItem>
{:else}
  <Overlay
    bind:open={actions.overlays.tag}
    variant="floating"
    size="none"
    contentProps={context.overlayContentProps}
  >
    {#snippet trigger()}
      <ButtonGroupItem
        variant="default"
        size="md"
        class={actionButtonVariants({ layout: context.layout })}
        aria-label="Manage tags"
        tooltip="Manage tags"
        disableTooltip={actions.overlays.tag}
      >
        <Icon icon="lucide:tag" class={iconClass} />
      </ButtonGroupItem>
    {/snippet}
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
  </Overlay>
{/if}
