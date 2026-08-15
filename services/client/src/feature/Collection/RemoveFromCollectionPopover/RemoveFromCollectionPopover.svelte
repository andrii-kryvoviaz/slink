<script lang="ts">
  import { Button } from '@slink/ui/components/button';

  import Icon from '@iconify/svelte';

  interface Props {
    loading?: boolean;
    close: () => void;
    confirm: () => void;
  }

  let { loading = false, close, confirm }: Props = $props();
</script>

<div class="w-full max-w-sm p-2 space-y-4">
  <div class="flex items-center gap-3">
    <div
      class="flex h-10 w-10 items-center justify-center rounded-full bg-danger/12 border border-danger-border/40 dark:border-danger-border/12 shadow-sm shrink-0"
    >
      <Icon icon="ph:folder-open" class="h-5 w-5 text-danger" />
    </div>
    <div>
      <h3 class="text-sm font-semibold text-foreground">
        Remove from Collection
      </h3>
      <p class="text-xs text-foreground-muted">
        Image will remain in your library
      </p>
    </div>
  </div>

  <div class="flex gap-3 pt-2">
    <Button
      variant="glass"
      rounded="full"
      size="sm"
      onclick={close}
      class="flex-1"
      disabled={loading}
    >
      Cancel
    </Button>
    <Button
      variant="danger"
      rounded="full"
      size="sm"
      onclick={confirm}
      justify="center"
      class="flex-1 font-medium shadow-lg hover:shadow-xl transition-all duration-200"
      {loading}
    >
      {#snippet leftIcon()}
        <Icon icon="ph:minus-circle" class="h-4 w-4" />
      {/snippet}
      Remove
    </Button>
  </div>
</div>
