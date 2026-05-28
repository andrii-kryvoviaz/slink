<script lang="ts">
  import { Button } from '@slink/ui/components/button';

  import Icon from '@iconify/svelte';

  interface Props {
    loading?: boolean;
    onConfirm: () => void;
    onCancel: () => void;
  }

  let { loading = false, onConfirm, onCancel }: Props = $props();
</script>

<div class="w-full min-w-56 p-2 space-y-4">
  <div class="flex items-center gap-3">
    <div
      class="flex h-10 w-10 items-center justify-center rounded-full bg-red-500/15 border border-red-500/20 shrink-0"
    >
      <Icon icon="ph:chat-circle-text" class="h-5 w-5 text-red-400" />
    </div>
    <div>
      <h3 class="text-sm font-semibold text-white">Delete Comment</h3>
      <p class="text-xs text-white/50">Comment will be permanently removed</p>
    </div>
  </div>

  <div class="flex gap-3 pt-2">
    <Button
      variant="glass-dark"
      rounded="full"
      size="sm"
      onclick={onCancel}
      class="flex-1"
      disabled={loading}
    >
      Cancel
    </Button>
    <Button
      variant="danger"
      rounded="full"
      size="sm"
      onclick={onConfirm}
      justify="center"
      class="flex-1 font-medium shadow-lg hover:shadow-xl transition-all duration-200"
      {loading}
    >
      {#snippet leftIcon()}
        <Icon icon="heroicons:trash" class="h-4 w-4" />
      {/snippet}
      Delete
    </Button>
  </div>
</div>
