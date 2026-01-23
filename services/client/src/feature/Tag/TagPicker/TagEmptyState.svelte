<script lang="ts">
  import Icon from '@iconify/svelte';

  interface Props {
    onCreateNew?: () => void;
  }

  let { onCreateNew }: Props = $props();
</script>

<div class="flex flex-col items-center gap-3 py-6 px-4">
  <div
    class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center"
  >
    <Icon icon="ph:tag" class="w-5 h-5 text-gray-400 dark:text-gray-500" />
  </div>
  <p class="text-sm text-gray-500 dark:text-gray-400">No tags yet</p>
  {#if onCreateNew}
    <button
      type="button"
      onclick={onCreateNew}
      class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors"
    >
      Create your first tag
    </button>
  {/if}
</div>
