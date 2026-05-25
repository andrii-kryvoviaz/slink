<script lang="ts">
  import { TagDeletePopover, TagMoveDialog } from '@slink/feature/Tag';
  import {
    ActionsMenu,
    DropdownSimpleGroup,
    DropdownSimpleItem,
  } from '@slink/ui/components';
  import type { ActionsMenuTone } from '@slink/ui/components';

  import Icon from '@iconify/svelte';

  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { Tag } from '@slink/api/Resources/TagResource';

  interface Props {
    tag: Tag;
    onDelete: (tag: Tag) => Promise<void>;
    onMove: (tagId: string, newParentId: string | null) => Promise<void>;
    tone?: ActionsMenuTone;
    triggerClass?: string;
  }

  let {
    tag,
    onDelete,
    onMove,
    tone = 'surface',
    triggerClass,
  }: Props = $props();

  let deleteConfirmOpen = $state(false);
  let moveDialogOpen = $state(false);
  let dropdownOpen = $state(false);

  const {
    isLoading: deleteIsLoading,
    error: deleteError,
    run: deleteTag,
  } = ReactiveState((t: Tag) => onDelete(t), { minExecutionTime: 300 });

  const handleDeleteClick = () => {
    deleteConfirmOpen = true;
  };

  const confirmDelete = async () => {
    await deleteTag(tag);

    if ($deleteError) {
      return;
    }

    deleteConfirmOpen = false;
    dropdownOpen = false;
  };

  const cancelDelete = () => {
    deleteConfirmOpen = false;
  };

  const handleMoveClick = () => {
    dropdownOpen = false;
    moveDialogOpen = true;
  };
</script>

<div class="flex items-center justify-end">
  <ActionsMenu
    bind:open={dropdownOpen}
    {tone}
    {triggerClass}
    label="Tag actions"
  >
    <DropdownSimpleGroup>
      {#if !deleteConfirmOpen}
        <DropdownSimpleItem on={{ click: handleMoveClick }}>
          {#snippet icon()}
            <Icon icon="lucide:arrow-right-left" class="h-4 w-4" />
          {/snippet}
          <span>Move</span>
        </DropdownSimpleItem>
        <DropdownSimpleItem
          danger={true}
          on={{ click: handleDeleteClick }}
          closeOnSelect={false}
        >
          {#snippet icon()}
            <Icon icon="heroicons:trash" class="h-4 w-4" />
          {/snippet}
          <span>Delete</span>
        </DropdownSimpleItem>
      {:else}
        <TagDeletePopover
          {tag}
          loading={$deleteIsLoading}
          onConfirm={confirmDelete}
          onCancel={cancelDelete}
        />
      {/if}
    </DropdownSimpleGroup>
  </ActionsMenu>
</div>

<TagMoveDialog
  {tag}
  bind:open={moveDialogOpen}
  onOpenChange={(o) => (moveDialogOpen = o)}
  {onMove}
/>
