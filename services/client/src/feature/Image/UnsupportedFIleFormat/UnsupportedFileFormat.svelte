<script lang="ts">
  import Icon from '@iconify/svelte';

  interface Props {
    oncloseToast?: () => void;
  }

  let { oncloseToast }: Props = $props();

  const handleClose = () => {
    oncloseToast?.();
  };
</script>

<div
  class="flex items-start gap-3 p-4 bg-purple-25/50 text-purple-700 border border-purple-200/20 shadow-md shadow-purple-500/4 backdrop-blur-xl dark:bg-purple-950/50 dark:text-purple-200 dark:border-purple-800/20 dark:shadow-purple-900/10 rounded-xl"
>
  <div
    class="flex h-8 w-8 items-center justify-center rounded-full bg-purple-100/60 dark:bg-purple-950/60 shrink-0 mt-0.5"
  >
    <Icon
      icon="clarity:block-line"
      class="h-4 w-4 text-purple-600 dark:text-purple-400"
    />
  </div>
  <div class="flex-1 min-w-0">
    <div class="text-sm font-medium text-purple-800 dark:text-purple-200 mb-1">
      Unsupported file format
    </div>
    <p class="text-sm text-purple-700 dark:text-purple-300/90 mb-2">
      The file you're trying to upload is not supported. Please use one of the
      supported image formats.
    </p>
    <a
      href="/help/faq#supported-image-formats"
      class="inline-flex items-center gap-1 text-sm font-medium text-purple-700 hover:text-purple-800 dark:text-purple-300 dark:hover:text-purple-200 underline underline-offset-2 transition-colors duration-200"
    >
      <Icon icon="heroicons:arrow-top-right-on-square" class="h-3 w-3" />
      View supported formats
    </a>
  </div>
  <button
    type="button"
    class="shrink-0 flex h-8 w-8 items-center justify-center rounded-full text-purple-600 dark:text-purple-400 hover:bg-purple-100/60 dark:hover:bg-purple-950/60 focus:outline-none focus:ring-2 focus:ring-purple-500/20 transition-colors duration-200"
    aria-label="Close notification"
    onclick={handleClose}
  >
    <span class="sr-only">Close</span>
    <Icon icon="heroicons:x-mark" class="h-4 w-4" />
  </button>
</div>
