<script lang="ts">
  import { CommentsSkeleton } from '@slink/feature/Image';
  import { ScrollArea } from '@slink/ui/components/scroll-area/index.js';
  import { Skeleton } from '@slink/ui/components/skeleton';
  import { Tooltip } from '@slink/ui/components/tooltip';
  import { onDestroy } from 'svelte';

  import { page } from '$app/state';
  import { plural } from '$lib/utils/i18n';
  import Icon from '@iconify/svelte';

  import type { AuthenticatedUser } from '@slink/api/Response';

  import { SortOrder, toggleSortOrder } from '@slink/lib/enum/SortOrder';
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

  const { settings } = page.data;

  let state = $state<CommentListState | null>(null);

  let {
    hasLoaded,
    count,
    isEmpty,
    comments,
    hasCurrentUser,
    replyingTo,
    editingComment,
    hasMore,
    remaining,
    isLoadingMore,
  } = $derived({
    hasLoaded: state?.hasLoaded ?? false,
    count: state?.count ?? 0,
    isEmpty: state?.isEmpty ?? true,
    comments: state?.comments ?? [],
    hasCurrentUser: state?.hasCurrentUser ?? false,
    replyingTo: state?.replyingTo ?? null,
    editingComment: state?.editingComment ?? null,
    hasMore: state?.hasMore ?? false,
    remaining: state?.remaining ?? 0,
    isLoadingMore: state?.isLoadingMore ?? false,
  });

  $effect(() => {
    if (isActive && !state) {
      state = CommentListState.create({
        imageId,
        imageOwnerId,
        currentUser,
        getSortOrder: () => settings.comment.sortOrder,
      });
      state?.load();
    }
  });

  $effect(() => {
    if (!isActive && state) {
      state.destroy();
      state = null;
    }
  });

  onDestroy(() => {
    state?.destroy();
  });

  function handleToggleSortOrder() {
    settings.comment = {
      sortOrder: toggleSortOrder(settings.comment.sortOrder),
    };
  }
</script>

{#snippet loadMoreDivider()}
  {#if isLoadingMore}
    {#each Array(2) as _}
      <div class="flex gap-3">
        <Skeleton
          class="w-8 h-8 rounded-full shrink-0 bg-on-surface-inverse/10"
        />
        <div class="flex-1 space-y-2">
          <Skeleton class="h-3 w-24 bg-on-surface-inverse/10" />
          <Skeleton class="h-3 w-3/4 bg-on-surface-inverse/10" />
        </div>
      </div>
    {/each}
  {:else}
    <button
      onclick={() => state?.loadMore()}
      class="flex w-full items-center gap-3 py-1 rounded text-[11.5px] text-on-surface-inverse/45 hover:text-on-surface-inverse/80 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-on-surface-inverse/30"
    >
      <span class="h-px flex-1 bg-on-surface-inverse/12"></span>
      <span>{plural(remaining, ['# more comment', '# more comments'])}</span>
      <span class="h-px flex-1 bg-on-surface-inverse/12"></span>
    </button>
  {/if}
{/snippet}

{#if !hasLoaded}
  <CommentsSkeleton />
{:else}
  <div
    class="flex flex-col w-full h-full bg-on-surface-inverse/5 backdrop-blur-sm rounded-2xl p-4"
  >
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-sm font-medium text-on-surface-inverse/80">Comments</h3>
      <div class="flex items-center gap-1">
        {#if count > 0}
          <Tooltip side="bottom" size="xs" variant="dark">
            {#snippet trigger()}
              <button
                onclick={handleToggleSortOrder}
                class="flex items-center justify-center w-6 h-6 rounded hover:bg-on-surface-inverse/10 transition-colors"
              >
                <Icon
                  icon={settings.comment.sortOrder === SortOrder.Asc
                    ? 'heroicons:bars-arrow-up'
                    : 'heroicons:bars-arrow-down'}
                  class="w-4 h-4 text-on-surface-inverse/40 hover:text-on-surface-inverse/60"
                />
              </button>
            {/snippet}
            {settings.comment.sortOrder === SortOrder.Asc
              ? 'Oldest first'
              : 'Newest first'}
          </Tooltip>
          <span class="text-xs text-on-surface-inverse/40 leading-6"
            >{count}</span
          >
        {/if}
      </div>
    </div>

    <ScrollArea
      class="flex-1 min-h-0 [&_[data-slot=scroll-area-thumb]]:bg-on-surface-inverse/20"
      orientation="vertical"
      type="scroll"
      onwheel={(e) => e.stopPropagation()}
    >
      {#if isEmpty}
        <div
          class="flex flex-col items-center justify-center py-8 text-on-surface-inverse/40"
        >
          <Icon
            icon="heroicons:chat-bubble-left-right"
            class="w-10 h-10 mb-3 text-on-surface-inverse/20"
          />
          <p class="text-sm">No comments yet</p>
          <p class="text-xs mt-1">Be the first to comment</p>
        </div>
      {:else}
        <div class="space-y-3 pr-2">
          {#if hasMore && settings.comment.sortOrder === SortOrder.Asc}
            {@render loadMoreDivider()}
          {/if}
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
          {#if hasMore && settings.comment.sortOrder === SortOrder.Desc}
            {@render loadMoreDivider()}
          {/if}
        </div>
      {/if}
    </ScrollArea>

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
      <div class="mt-4 pt-4 border-t border-on-surface-inverse/10 text-center">
        <p class="text-sm text-on-surface-inverse/50">Sign in to comment</p>
      </div>
    {/if}
  </div>
{/if}
