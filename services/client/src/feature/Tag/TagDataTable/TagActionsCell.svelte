<script lang="ts">
  import { TagDeletePopover, TagMoveDialog } from '@slink/feature/Tag';
  import {
    DropdownSimple,
    DropdownSimpleGroup,
    DropdownSimpleItem,
  } from '@slink/ui/components';
  import { Button } from '@slink/ui/components/button';

  import Icon from '@iconify/svelte';

  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { Tag } from '@slink/api/Resources/TagResource';

  interface Props {
    tag: Tag;
    onDelete: (tag: Tag) => Promise<void>;
    onMove: (tagId: string, newParentId: string | null) => Promise<void>;
  }

  let { tag, onDelete, onMove }: Props = $props();

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
  <DropdownSimple bind:open={dropdownOpen} variant="invisible" size="xs">
    {#snippet trigger(triggerProps)}
      <Button
        variant="glass"
        size="icon"
        padding="none"
        rounded="md"
        {...triggerProps}
        aria-label="Tag actions"
      >
        <Icon icon="lucide:ellipsis" class="h-4 w-4" />
      </Button>
    {/snippet}

    <DropdownSimpleGroup>
      {#if !deleteConfirmOpen}
        <DropdownSimpleItem on={{ click: handleMoveClick }}>
          {#snippet icon()}
            <Icon icon="lucide:git-branch" class="h-4 w-4" />
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
  </DropdownSimple>
</div>

<TagMoveDialog
  {tag}
  bind:open={moveDialogOpen}
  onOpenChange={(o) => (moveDialogOpen = o)}
  {onMove}
/>
