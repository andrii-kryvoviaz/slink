<script lang="ts">
  import { Button } from '@slink/ui/components/button';

  import Icon from '@iconify/svelte';

  import type { CommentItem } from '@slink/api/Response';

  interface Props {
    replyingTo: CommentItem | null;
    editingComment: CommentItem | null;
    onSubmit: (content: string) => Promise<void>;
    onSaveEdit: (content: string) => Promise<void>;
    onCancelReply: () => void;
    onCancelEdit: () => void;
  }

  let {
    replyingTo,
    editingComment,
    onSubmit,
    onSaveEdit,
    onCancelReply,
    onCancelEdit,
  }: Props = $props();

  let content = $state('');
  let isSubmitting = $state(false);
  let textArea: HTMLTextAreaElement | undefined = $state();

  let isEditing = $derived(editingComment !== null);
  let activeRef = $derived(editingComment?.referencedComment ?? replyingTo);

  async function handleSubmit() {
    if (!content.trim() || isSubmitting) return;

    isSubmitting = true;
    try {
      if (isEditing) {
        await onSaveEdit(content.trim());
      } else {
        await onSubmit(content.trim());
      }
      content = '';
    } catch (error) {
      console.error('Failed to submit comment:', error);
    } finally {
      isSubmitting = false;
    }
  }

  function handleCancel() {
    if (isEditing) {
      onCancelEdit();
    } else {
      onCancelReply();
    }
    content = '';
  }

  function handleKeyDown(event: KeyboardEvent) {
    if (event.key === 'Enter' && (event.metaKey || event.ctrlKey)) {
      event.preventDefault();
      handleSubmit();
    }
    if (event.key === 'Escape' && (replyingTo || isEditing)) {
      handleCancel();
    }
  }

  $effect(() => {
    if (editingComment) {
      content = editingComment.content.decodeHtmlEntities();
      textArea?.focus();
    }
  });

  $effect(() => {
    if (replyingTo && textArea) {
      textArea.focus();
    }
  });
</script>

<div class="mt-4 pt-4 border-t border-white/10">
  {#if activeRef}
    <div class="mb-2 px-3 py-2 bg-white/5 rounded-lg">
      <div class="flex items-center justify-between mb-1">
        <span class="text-xs text-white/60">
          {#if isEditing}
            Editing reply to <span class="font-medium text-white/80"
              >@{activeRef.author.displayName}</span
            >
          {:else}
            Replying to <span class="font-medium text-white/80"
              >@{activeRef.author.displayName}</span
            >
          {/if}
        </span>
        <button
          onclick={handleCancel}
          class="p-1 text-white/40 hover:text-white/80 transition-colors"
        >
          <Icon icon="heroicons:x-mark" class="w-3 h-3" />
        </button>
      </div>
      <p class="text-xs text-white/50 line-clamp-2">
        {@html activeRef.displayContent.toFormattedHtml()}
      </p>
    </div>
  {/if}

  <div
    class="relative flex items-center bg-white/10 border border-white/20 rounded-full transition-all duration-200 focus-within:ring-1 focus-within:ring-white/30 focus-within:border-white/30"
  >
    <textarea
      bind:this={textArea}
      bind:value={content}
      onkeydown={handleKeyDown}
      class="flex-1 px-4 py-2.5 pr-12 text-sm bg-transparent text-white/90 placeholder-white/40 resize-none focus:outline-none"
      rows="1"
      placeholder={isEditing
        ? 'Edit your comment...'
        : activeRef
          ? `Reply to @${activeRef.author.displayName}...`
          : 'Write a comment...'}
      disabled={isSubmitting}
    ></textarea>
    <div class="absolute right-1.5 flex items-center gap-1">
      {#if isEditing}
        <Button
          variant="ghost"
          size="icon"
          rounded="full"
          onclick={handleCancel}
          disabled={isSubmitting}
          class="size-8! text-white/40 hover:text-white/80 hover:bg-white/10"
        >
          <Icon icon="heroicons:x-mark" class="w-4 h-4" />
        </Button>
      {/if}
      <Button
        variant="primary-dark"
        size="icon"
        rounded="full"
        onclick={handleSubmit}
        disabled={!content.trim() || isSubmitting}
        class="size-8!"
      >
        {#if isSubmitting}
          <Icon icon="heroicons:arrow-path" class="w-4 h-4 animate-spin" />
        {:else if isEditing}
          <Icon icon="heroicons:check" class="w-4 h-4" />
        {:else}
          <Icon icon="heroicons:paper-airplane" class="w-4 h-4" />
        {/if}
      </Button>
    </div>
  </div>
  <p class="text-xs text-white/30 mt-2 pl-4">
    Use **bold** and *italic* for formatting • ⌘+Enter to submit
  </p>
</div>
