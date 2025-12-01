<script lang="ts">
  import { FormattedDate, HashtagText } from '@slink/feature/Text';
  import { UserAvatar } from '@slink/feature/User';
  import {
    DropdownSimple,
    DropdownSimpleGroup,
    DropdownSimpleItem,
  } from '@slink/ui/components';
  import * as HoverCard from '@slink/ui/components/hover-card';
  import { Tooltip } from '@slink/ui/components/tooltip';

  import Icon from '@iconify/svelte';

  import type { CommentItem } from '@slink/api/Response';

  import CommentDeleteConfirmation from './CommentDeleteConfirmation.svelte';

  interface Props {
    comment: CommentItem;
    editingCommentId: string | null;
    currentUserId: string | null;
    imageOwnerId: string;
    onReply: () => void;
    onEdit: () => void;
    onDelete: () => void;
    onHashtagClick?: () => void;
  }

  let {
    comment,
    editingCommentId,
    currentUserId,
    imageOwnerId,
    onReply,
    onEdit,
    onDelete,
    onHashtagClick,
  }: Props = $props();

  let isEditing = $derived(editingCommentId === comment.id);
  let isAuthor = $derived(
    currentUserId !== null && comment.author.id === currentUserId,
  );
  let canEdit = $derived(isAuthor && comment.canEdit);
  let canDelete = $derived(
    currentUserId !== null &&
      !comment.isDeleted &&
      (comment.author.id === currentUserId || imageOwnerId === currentUserId),
  );

  let deleteConfirmOpen = $state(false);

  function handleDeleteClick() {
    deleteConfirmOpen = true;
  }

  function confirmDelete() {
    deleteConfirmOpen = false;
    onDelete();
  }

  function cancelDelete() {
    deleteConfirmOpen = false;
  }

  const showDropdown = $derived(
    (isAuthor || canDelete) && !comment.isDeleted && !isEditing,
  );
</script>

<div
  class="group flex gap-3 p-2 transition-colors {comment.isDeleted
    ? 'opacity-50'
    : ''} {isEditing
    ? 'bg-white/5 border-l-2 border-white/40'
    : 'hover:bg-white/5 rounded-lg'}"
>
  <HoverCard.Root openDelay={300} closeDelay={100}>
    <HoverCard.Trigger class="shrink-0 pt-0.5 cursor-pointer">
      <UserAvatar
        size="sm"
        user={{
          displayName: comment.author.displayName,
          email: comment.author.email,
        }}
      />
    </HoverCard.Trigger>
    <HoverCard.Content side="top" align="start" class="w-64 p-3">
      <div class="flex items-center gap-3">
        <UserAvatar
          size="md"
          user={{
            displayName: comment.author.displayName,
            email: comment.author.email,
          }}
        />
        <div class="flex-1 min-w-0">
          <p class="font-medium text-sm text-white">
            {comment.author.displayName}
          </p>
        </div>
      </div>
    </HoverCard.Content>
  </HoverCard.Root>

  <div class="flex-1 min-w-0">
    <div class="flex items-center gap-2 mb-1">
      <span class="text-sm font-medium text-white/90 truncate">
        {comment.author.displayName}
      </span>
      <span class="text-xs text-white/40 shrink-0">
        <FormattedDate date={comment.createdAt.timestamp} />
      </span>
      {#if comment.isEdited}
        <span class="text-xs text-white/30 shrink-0">(edited)</span>
      {/if}

      <div class="ml-auto shrink-0 flex items-center gap-1">
        {#if !comment.isDeleted && !isEditing}
          <Tooltip side="top" size="xs" variant="dark">
            {#snippet trigger()}
              <button
                onclick={onReply}
                class="p-1 text-white/40 hover:text-white/70 rounded transition-colors"
              >
                <Icon icon="heroicons:arrow-uturn-left" class="w-4 h-4" />
              </button>
            {/snippet}
            Reply
          </Tooltip>
        {/if}

        {#if showDropdown}
          <DropdownSimple variant="invisible" size="xs" contentVariant="dark">
            {#snippet trigger()}
              <button
                class="p-1 text-white/40 hover:text-white/70 rounded transition-colors"
              >
                <Icon icon="heroicons:ellipsis-vertical" class="w-4 h-4" />
              </button>
            {/snippet}

            {#if !deleteConfirmOpen}
              {#if isAuthor}
                <DropdownSimpleGroup>
                  <DropdownSimpleItem
                    variant="dark"
                    on={{ click: onEdit }}
                    disabled={!canEdit}
                  >
                    {#snippet icon()}
                      <Icon icon="heroicons:pencil" class="w-4 h-4" />
                    {/snippet}
                    Edit
                  </DropdownSimpleItem>
                </DropdownSimpleGroup>
              {/if}

              {#if canDelete}
                <DropdownSimpleGroup>
                  <DropdownSimpleItem
                    variant="dark"
                    danger
                    on={{ click: handleDeleteClick }}
                    closeOnSelect={false}
                  >
                    {#snippet icon()}
                      <Icon icon="heroicons:trash" class="w-4 h-4" />
                    {/snippet}
                    Delete
                  </DropdownSimpleItem>
                </DropdownSimpleGroup>
              {/if}
            {:else}
              <DropdownSimpleGroup>
                <CommentDeleteConfirmation
                  onConfirm={confirmDelete}
                  onCancel={cancelDelete}
                />
              </DropdownSimpleGroup>
            {/if}
          </DropdownSimple>
        {/if}
      </div>
    </div>

    {#if comment.referencedComment}
      <div class="mb-2 pl-2 border-l-2 border-white/20 text-xs text-white/50">
        <span class="font-medium"
          >@{comment.referencedComment.author.displayName}</span
        >
        {#if comment.referencedComment.isDeleted}
          <span class="ml-1 italic">[deleted]</span>
        {:else}
          <p class="mt-0.5 line-clamp-1">
            <HashtagText
              text={comment.referencedComment.displayContent}
              variant="glass"
              size="sm"
              onBeforeNavigate={onHashtagClick}
            />
          </p>
        {/if}
      </div>
    {/if}

    {#if comment.isDeleted}
      <p class="text-sm text-white/40 italic">This comment has been deleted</p>
    {:else}
      <p class="text-sm text-white/80 whitespace-pre-wrap wrap-break-word">
        <HashtagText
          text={comment.displayContent}
          variant="glass"
          size="sm"
          onBeforeNavigate={onHashtagClick}
        />
      </p>
    {/if}
  </div>
</div>
