<script lang="ts">
  import Icon from '@iconify/svelte';

  import BaseToast from './BaseToast.svelte';

  interface Props {
    message?: string;
    oncloseToast?: () => void;
  }

  let { message = '', oncloseToast }: Props = $props();
</script>

<BaseToast variant="purple" icon="clarity:block-line" {oncloseToast}>
  <div class="flex flex-col gap-2">
    <div class="text-sm font-medium text-purple-800 dark:text-purple-200">
      Unsupported file format
    </div>
    <p class="text-sm text-purple-700 dark:text-purple-300/90">
      {#if message}
        {@html message}
      {:else}
        The file you're trying to upload is not supported. Please use one of the
        supported image formats.
      {/if}
    </p>
    <a
      href="/help/faq#supported-image-formats"
      class="inline-flex items-center gap-1 text-sm font-medium text-purple-700 hover:text-purple-800 dark:text-purple-300 dark:hover:text-purple-200 underline underline-offset-2 transition-colors duration-200"
    >
      <Icon icon="heroicons:arrow-top-right-on-square" class="h-3 w-3" />
      View supported formats
    </a>
  </div>
</BaseToast>
