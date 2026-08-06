<script lang="ts">
  import Icon from '@iconify/svelte';

  interface Props {
    message?: string;
    oncloseToast?: () => void;
  }

  let { message = '', oncloseToast }: Props = $props();

  const handleClose = () => {
    oncloseToast?.();
  };
</script>

<div
  class="flex items-center gap-3 p-4 text-success-subtle-foreground border border-success-border/20 shadow-md shadow-success/4 backdrop-blur-xl dark:bg-success-subtle/50 rounded-xl"
>
  <div
    class="flex h-8 w-8 items-center justify-center rounded-full bg-success-subtle/60 shrink-0"
  >
    <Icon icon="heroicons:check-circle" class="h-4 w-4 text-success" />
  </div>
  <div class="flex-1 min-w-0">
    <p class="text-sm">
      {@html message}
    </p>
  </div>
  <button
    type="button"
    class="shrink-0 flex h-8 w-8 items-center justify-center rounded-full text-success hover:bg-success-subtle/60 focus:outline-none focus:ring-2 focus:ring-success/20 transition-colors duration-200"
    aria-label="Close notification"
    onclick={handleClose}
  >
    <span class="sr-only">Close</span>
    <Icon icon="heroicons:x-mark" class="h-4 w-4" />
  </button>
</div>
