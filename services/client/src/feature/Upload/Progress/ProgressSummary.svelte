<script lang="ts">
  import { useUploadProgress } from './context.svelte';

  const progress = useUploadProgress();
</script>

<p class="text-sm text-slate-500 dark:text-slate-400">
  {progress.completedCount} of {progress.totalCount} files uploaded
  {#if progress.hasErrors}
    • {progress.failedCount} failed
  {/if}
</p>
