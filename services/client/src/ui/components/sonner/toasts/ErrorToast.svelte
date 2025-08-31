<script lang="ts">
  import Icon from '@iconify/svelte';

  interface Props {
    message?: string;
    oncloseToast?: () => void;
  }

  let { message = '', oncloseToast } = $props();

  const handleClose = () => {
    oncloseToast?.();
  };
</script>

<div
  class="flex items-center gap-3 p-4 bg-red-25/50 text-red-700 border border-red-200/20 shadow-md shadow-red-500/4 backdrop-blur-xl dark:bg-red-950/50 dark:text-red-200 dark:border-red-800/20 dark:shadow-red-900/10 rounded-xl"
>
  <div
    class="flex h-8 w-8 items-center justify-center rounded-full bg-red-100/60 dark:bg-red-950/60 shrink-0"
  >
    <Icon
      icon="heroicons:x-circle"
      class="h-4 w-4 text-red-600 dark:text-red-400"
    />
  </div>
  <div class="flex-1 min-w-0">
    <p class="text-sm text-red-700 dark:text-red-300/90">
      {@html message}
    </p>
  </div>
  <button
    type="button"
    class="shrink-0 flex h-8 w-8 items-center justify-center rounded-full text-red-600 dark:text-red-400 hover:bg-red-100/60 dark:hover:bg-red-950/60 focus:outline-none focus:ring-2 focus:ring-red-500/20 transition-colors duration-200"
    aria-label="Close notification"
    onclick={handleClose}
  >
    <span class="sr-only">Close</span>
    <Icon icon="heroicons:x-mark" class="h-4 w-4" />
  </button>
</div>
