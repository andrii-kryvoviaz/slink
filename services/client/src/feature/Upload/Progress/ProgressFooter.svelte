<script lang="ts">
  import { bytesToSize } from '$lib/utils/bytesConverter';
  import Icon from '@iconify/svelte';

  import { useUploadProgress } from './context.svelte';

  interface Props {
    onViewAll?: () => void;
  }

  let { onViewAll }: Props = $props();

  const progress = useUploadProgress();
</script>

{#if progress.isCompleted && onViewAll}
  <div
    class="mt-3 pt-3 flex items-center justify-between border-t border-slate-200/50 dark:border-slate-700/50"
  >
    <span class="text-xs text-slate-500 dark:text-slate-400">
      {bytesToSize(progress.totalBytes)}
    </span>
    <button
      type="button"
      onclick={onViewAll}
      class="group/link inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400/50 focus-visible:ring-offset-2 focus-visible:ring-offset-transparent rounded"
    >
      View all
      <Icon
        icon="ph:arrow-right"
        class="w-3.5 h-3.5 transition-transform duration-200 group-hover/link:translate-x-0.5"
      />
    </button>
  </div>
{/if}
