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
  class="flex items-center gap-3 p-4 bg-blue-25/50 text-blue-700 border border-blue-200/20 shadow-md shadow-blue-500/4 backdrop-blur-xl dark:bg-blue-950/50 dark:text-blue-200 dark:border-blue-800/20 dark:shadow-blue-900/10 rounded-xl"
>
  <div
    class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100/60 dark:bg-blue-950/60 shrink-0"
  >
    <Icon
      icon="heroicons:information-circle"
      class="h-4 w-4 text-blue-600 dark:text-blue-400"
    />
  </div>
  <div class="flex-1 min-w-0">
    <p class="text-sm text-blue-700 dark:text-blue-300/90">
      {@html message}
    </p>
  </div>
  <button
    type="button"
    class="shrink-0 flex h-8 w-8 items-center justify-center rounded-full text-blue-600 dark:text-blue-400 hover:bg-blue-100/60 dark:hover:bg-blue-950/60 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-colors duration-200"
    aria-label="Close notification"
    onclick={handleClose}
  >
    <span class="sr-only">Close</span>
    <Icon icon="heroicons:x-mark" class="h-4 w-4" />
  </button>
</div>
