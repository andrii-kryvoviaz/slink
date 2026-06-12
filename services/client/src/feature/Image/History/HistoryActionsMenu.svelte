<script lang="ts">
  import {
    CollectionPicker,
    CreateCollectionDialog,
  } from '@slink/feature/Collection';
  import {
    ImageDeletePopover,
    createImageActionsState,
    historyActionBarButtons,
  } from '@slink/feature/Image';
  import { CreateTagDialog, TagPicker } from '@slink/feature/Tag';
  import {
    ActionsMenu,
    DropdownSimpleGroup,
    DropdownSimpleItem,
  } from '@slink/ui/components';
  import { Overlay } from '@slink/ui/components/popover';

  import Icon from '@iconify/svelte';

  import type { Tag } from '@slink/api/Resources/TagResource';
  import type { CollectionReference } from '@slink/api/Response/Collection/CollectionResponse';

  interface Props {
    image: {
      id: string;
      fileName: string;
      isPublic: boolean;
      collectionIds?: string[];
      tagIds?: string[];
    };
    on?: {
      imageDelete?: (imageId: string) => void;
      collectionChange?: (
        imageId: string,
        collections: CollectionReference[],
      ) => void;
      tagChange?: (imageId: string, tags: Tag[]) => void;
    };
  }

  let { image = $bindable(), on }: Props = $props();

  let menuOpen = $state(false);
  let anchor = $state<HTMLElement>();

  const actions = createImageActionsState({
    getImage: () => image,
    onImageUpdate: (updated) => (image = updated),
    onImageDelete: (id) => on?.imageDelete?.(id),
    onCollectionChange: (id, collections) =>
      on?.collectionChange?.(id, collections),
    onTagChange: (id, tags) => on?.tagChange?.(id, tags),
  });

  const visibleButtons = $derived(
    actions.filterVisibleButtons(historyActionBarButtons),
  );

  const openCollectionPicker = () => {
    menuOpen = false;
    actions.popover.collection = true;
  };

  const openTagPicker = () => {
    menuOpen = false;
    actions.popover.tag = true;
  };

  const openDeleteConfirm = () => {
    menuOpen = false;
    actions.popover.delete = true;
  };
</script>

<div class="flex items-center justify-end" bind:this={anchor}>
  <ActionsMenu bind:open={menuOpen} tone="surface" label="Image actions">
    <DropdownSimpleGroup>
      <DropdownSimpleItem on={{ click: actions.handleDownload }}>
        {#snippet icon()}
          <Icon icon="lucide:download" class="h-4 w-4" />
        {/snippet}
        <span>Download</span>
      </DropdownSimpleItem>
      <DropdownSimpleItem on={{ click: openCollectionPicker }}>
        {#snippet icon()}
          <Icon icon="lucide:folder" class="h-4 w-4" />
        {/snippet}
        <span>Add to collection</span>
      </DropdownSimpleItem>
      <DropdownSimpleItem on={{ click: openTagPicker }}>
        {#snippet icon()}
          <Icon icon="lucide:tag" class="h-4 w-4" />
        {/snippet}
        <span>Manage tags</span>
      </DropdownSimpleItem>
      <DropdownSimpleItem on={{ click: actions.handleCopy }}>
        {#snippet icon()}
          <Icon icon="lucide:link" class="h-4 w-4" />
        {/snippet}
        <span>Copy link</span>
      </DropdownSimpleItem>
      {#if visibleButtons.includes('visibility')}
        <DropdownSimpleItem on={{ click: actions.handleVisibilityChange }}>
          {#snippet icon()}
            <Icon icon={actions.visibilityIcon} class="h-4 w-4" />
          {/snippet}
          {#if image.isPublic}
            <span>Make private</span>
          {:else}
            <span>Make public</span>
          {/if}
        </DropdownSimpleItem>
      {/if}
      <DropdownSimpleItem danger={true} on={{ click: openDeleteConfirm }}>
        {#snippet icon()}
          <Icon icon="lucide:trash-2" class="h-4 w-4" />
        {/snippet}
        <span>Delete image</span>
      </DropdownSimpleItem>
    </DropdownSimpleGroup>
  </ActionsMenu>
</div>

<Overlay
  bind:open={actions.popover.collection}
  variant="floating"
  size="none"
  triggerClass="hidden"
  contentProps={{ align: 'end', customAnchor: anchor }}
>
  <CollectionPicker
    pickerState={actions.collectionPickerState}
    createModalState={actions.createCollectionModalState}
    variant="popover"
    onToggle={actions.handleCollectionToggle}
    onBeforeCreate={actions.popover.suspend}
    onAfterClose={actions.popover.restore}
  />
</Overlay>

<Overlay
  bind:open={actions.popover.tag}
  variant="floating"
  size="none"
  triggerClass="hidden"
  contentProps={{ align: 'end', customAnchor: anchor }}
>
  <TagPicker
    pickerState={actions.tagPickerState}
    createModalState={actions.createTagModalState}
    variant="popover"
    onToggle={actions.handleTagToggle}
    onBeforeCreate={actions.popover.suspend}
    onAfterClose={actions.popover.restore}
  />
</Overlay>

<Overlay
  bind:open={actions.popover.delete}
  variant="floating"
  triggerClass="hidden"
  contentProps={{ align: 'end', customAnchor: anchor }}
>
  <ImageDeletePopover
    loading={actions.deleteIsLoading}
    close={() => (actions.popover.delete = false)}
    confirm={({ preserveOnDiskAfterDeletion }) =>
      actions.handleDelete(preserveOnDiskAfterDeletion)}
  />
</Overlay>

<CreateCollectionDialog modalState={actions.createCollectionModalState} />
<CreateTagDialog modalState={actions.createTagModalState} />
