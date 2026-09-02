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
  class="flex items-center gap-3 p-4 text-danger-text border border-danger-border/20 dark:border-danger-border/6 shadow-md shadow-danger/4 backdrop-blur-xl bg-danger-wash dark:bg-danger-wash/20 rounded-xl"
>
  <div
    class="flex h-8 w-8 items-center justify-center rounded-full bg-danger/15 shrink-0"
  >
    <Icon icon="heroicons:x-circle" class="h-4 w-4 text-danger" />
  </div>
  <div class="flex-1 min-w-0">
    <p class="text-sm">
      {@html message}
    </p>
  </div>
  <button
    type="button"
    class="shrink-0 flex h-8 w-8 items-center justify-center rounded-full text-danger hover:bg-danger/15 focus:outline-none focus:ring-2 focus:ring-danger/20 transition-colors duration-200"
    aria-label="Close notification"
    onclick={handleClose}
  >
    <span class="sr-only">Close</span>
    <Icon icon="heroicons:x-mark" class="h-4 w-4" />
  </button>
</div>
