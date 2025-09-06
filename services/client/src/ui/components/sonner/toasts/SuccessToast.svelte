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
  class="flex items-center gap-3 p-4 bg-green-25/50 text-green-700 border border-green-200/20 shadow-md shadow-green-500/4 backdrop-blur-xl dark:bg-green-950/50 dark:text-green-200 dark:border-green-800/20 dark:shadow-green-900/10 rounded-xl"
>
  <div
    class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100/60 dark:bg-green-950/60 shrink-0"
  >
    <Icon
      icon="heroicons:check-circle"
      class="h-4 w-4 text-green-600 dark:text-green-400"
    />
  </div>
  <div class="flex-1 min-w-0">
    <p class="text-sm text-green-700 dark:text-green-300/90">
      {@html message}
    </p>
  </div>
  <button
    type="button"
    class="shrink-0 flex h-8 w-8 items-center justify-center rounded-full text-green-600 dark:text-green-400 hover:bg-green-100/60 dark:hover:bg-green-950/60 focus:outline-none focus:ring-2 focus:ring-green-500/20 transition-colors duration-200"
    aria-label="Close notification"
    onclick={handleClose}
  >
    <span class="sr-only">Close</span>
    <Icon icon="heroicons:x-mark" class="h-4 w-4" />
  </button>
</div>
