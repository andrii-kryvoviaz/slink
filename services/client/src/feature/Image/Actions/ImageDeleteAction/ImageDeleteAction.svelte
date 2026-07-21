<script lang="ts">
  import { ImageDeletePopover } from '@slink/feature/Image';
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
    actions.overlays.delete = true;
  };
</script>

{#if display === 'item'}
  <DropdownSimpleItem danger={true} on={{ click: openFromMenu }}>
    {#snippet icon()}
      <Icon icon="lucide:trash-2" class="h-4 w-4" />
    {/snippet}
    <span>Delete image</span>
  </DropdownSimpleItem>
{:else}
  <Overlay
    bind:open={actions.overlays.delete}
    variant="floating"
    contentProps={context.overlayContentProps}
  >
    {#snippet trigger()}
      <ButtonGroupItem
        variant="destructive"
        size="md"
        class={actionButtonVariants({
          layout: context.layout,
          variant: 'destructive',
        })}
        aria-label="Delete image"
        disabled={actions.deleteIsLoading}
        tooltip="Delete image"
        disableTooltip={actions.overlays.delete}
      >
        <Icon icon="lucide:trash-2" class={iconClass} />
      </ButtonGroupItem>
    {/snippet}
    <ImageDeletePopover
      loading={actions.deleteIsLoading}
      close={() => (actions.overlays.delete = false)}
      confirm={({ preserveOnDiskAfterDeletion }) =>
        actions.handleDelete(preserveOnDiskAfterDeletion)}
    />
  </Overlay>
{/if}
