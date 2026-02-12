<script lang="ts">
  import { TagDeletePopover, TagMoveDialog } from '@slink/feature/Tag';
  import {
    DropdownSimple,
    DropdownSimpleGroup,
    DropdownSimpleItem,
  } from '@slink/ui/components';

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

<div class="flex items-center justify-end gap-2 pr-2">
  <DropdownSimple bind:open={dropdownOpen} variant="invisible" size="xs">
    {#snippet trigger(triggerProps)}
      <button
        {...triggerProps}
        class="group relative flex items-center justify-center h-8 w-8 rounded-md bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm border border-gray-200/60 dark:border-gray-700/60 text-gray-500 dark:text-gray-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/50 hover:border-slate-300 dark:hover:border-slate-600 active:scale-95 focus-visible:ring-slate-500/30 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 transition-all duration-200 ease-out"
        aria-label="Tag actions"
      >
        <Icon icon="lucide:ellipsis" class="h-4 w-4" />
      </button>
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
