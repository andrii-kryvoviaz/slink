<script lang="ts">
  import { useUploadProgress } from './context.svelte';

  const progress = useUploadProgress();
</script>

<p class="text-sm text-muted-foreground">
  <span>{progress.completedCount} of {progress.totalCount} files uploaded</span>
  {#if progress.hasErrors}
    <span>• {progress.failedCount} failed</span>
  {/if}
</p>
