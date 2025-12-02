<script lang="ts">
  import { CommentsSkeleton } from '@slink/feature/Image';
  import { Tooltip } from '@slink/ui/components/tooltip';
  import { onDestroy } from 'svelte';

  import Icon from '@iconify/svelte';

  import type { AuthenticatedUser } from '@slink/api/Response';

  import { SortOrder } from '@slink/lib/enum/SortOrder';
  import { settings } from '@slink/lib/settings';
  import { CommentListState } from '@slink/lib/state/CommentListState.svelte';

  import CommentInput from './CommentInput.svelte';
  import CommentListItem from './CommentListItem.svelte';

  interface Props {
    imageId: string;
    imageOwnerId: string;
    currentUser: AuthenticatedUser | null;
    isActive?: boolean;
    onClose?: () => void;
  }

  let {
    imageId,
    imageOwnerId,
    currentUser,
    isActive = false,
    onClose,
  }: Props = $props();

  const { sortOrder } = settings.get('comment', { sortOrder: SortOrder.Asc });

  let state = $state<CommentListState | null>(null);

  let {
    hasLoaded,
    count,
    isEmpty,
    comments,
    hasCurrentUser,
    replyingTo,
    editingComment,
  } = $derived({
    hasLoaded: state?.hasLoaded ?? false,
    count: state?.count ?? 0,
    isEmpty: state?.isEmpty ?? true,
    comments: state?.comments ?? [],
    hasCurrentUser: state?.hasCurrentUser ?? false,
    replyingTo: state?.replyingTo ?? null,
    editingComment: state?.editingComment ?? null,
  });

  $effect(() => {
    if (isActive && !state) {
      state = CommentListState.create({
        imageId,
        imageOwnerId,
        currentUserId: currentUser?.id ?? null,
        sortOrder,
      });
      state?.load();
    }
  });

  onDestroy(() => {
    state?.destroy();
  });

  function toggleSortOrder() {
    settings.set('comment', {
      sortOrder: $sortOrder === SortOrder.Asc ? SortOrder.Desc : SortOrder.Asc,
    });
  }
</script>

{#if !hasLoaded}
  <CommentsSkeleton />
{:else}
  <div
    class="flex flex-col w-full h-full bg-white/5 backdrop-blur-sm rounded-2xl p-4"
  >
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-sm font-medium text-white/80">Comments</h3>
      <div class="flex items-center gap-1">
        {#if count > 0}
          <Tooltip side="bottom" size="xs" variant="dark">
            {#snippet trigger()}
              <button
                onclick={toggleSortOrder}
                class="flex items-center justify-center w-6 h-6 rounded hover:bg-white/10 transition-colors"
              >
                <Icon
                  icon={$sortOrder === SortOrder.Asc
                    ? 'heroicons:bars-arrow-up'
                    : 'heroicons:bars-arrow-down'}
                  class="w-4 h-4 text-white/40 hover:text-white/60"
                />
              </button>
            {/snippet}
            {$sortOrder === SortOrder.Asc ? 'Oldest first' : 'Newest first'}
          </Tooltip>
          <span class="text-xs text-white/40 leading-6">{count}</span>
        {/if}
      </div>
    </div>

    <div
      class="flex-1 min-h-0 overflow-y-auto"
      onwheel={(e) => e.stopPropagation()}
    >
      {#if isEmpty}
        <div
          class="flex flex-col items-center justify-center py-8 text-white/40"
        >
          <Icon
            icon="heroicons:chat-bubble-left-right"
            class="w-10 h-10 mb-3 text-white/20"
          />
          <p class="text-sm">No comments yet</p>
          <p class="text-xs mt-1">Be the first to comment</p>
        </div>
      {:else}
        {#key count}
          <div class="space-y-3 pr-2">
            {#each comments as comment (comment.id)}
              <CommentListItem
                {comment}
                editingCommentId={editingComment?.id ?? null}
                currentUserId={currentUser?.id ?? null}
                {imageOwnerId}
                onReply={() => state?.startReply(comment)}
                onEdit={() => state?.startEdit(comment)}
                onDelete={() => state?.deleteComment(comment.id)}
                onHashtagClick={onClose}
              />
            {/each}
          </div>
        {/key}
      {/if}
    </div>

    {#if hasCurrentUser && state}
      <CommentInput
        {replyingTo}
        {editingComment}
        onSubmit={(content) => state!.createComment(content)}
        onSaveEdit={(content) =>
          state!.updateComment(editingComment!.id, content)}
        onCancelReply={() => state!.cancelReply()}
        onCancelEdit={() => state!.cancelEdit()}
      />
    {:else}
      <div class="mt-4 pt-4 border-t border-white/10 text-center">
        <p class="text-sm text-white/50">Sign in to comment</p>
      </div>
    {/if}
  </div>
{/if}
