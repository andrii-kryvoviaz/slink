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
  class="flex items-center gap-3 p-4 bg-yellow-25/50 text-yellow-700 border border-yellow-200/20 shadow-md shadow-yellow-500/4 backdrop-blur-xl dark:bg-yellow-950/50 dark:text-yellow-200 dark:border-yellow-800/20 dark:shadow-yellow-900/10 rounded-xl"
>
  <div
    class="flex h-8 w-8 items-center justify-center rounded-full bg-yellow-100/60 dark:bg-yellow-950/60 shrink-0"
  >
    <Icon
      icon="heroicons:exclamation-triangle"
      class="h-4 w-4 text-yellow-600 dark:text-yellow-400"
    />
  </div>
  <div class="flex-1 min-w-0">
    <p class="text-sm text-yellow-700 dark:text-yellow-300/90">
      {@html message}
    </p>
  </div>
  <button
    type="button"
    class="shrink-0 flex h-8 w-8 items-center justify-center rounded-full text-yellow-600 dark:text-yellow-400 hover:bg-yellow-100/60 dark:hover:bg-yellow-950/60 focus:outline-none focus:ring-2 focus:ring-yellow-500/20 transition-colors duration-200"
    aria-label="Close notification"
    onclick={handleClose}
  >
    <span class="sr-only">Close</span>
    <Icon icon="heroicons:x-mark" class="h-4 w-4" />
  </button>
</div>
