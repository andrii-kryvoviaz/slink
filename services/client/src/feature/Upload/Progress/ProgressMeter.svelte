<script lang="ts">
  import { Progress } from '@slink/ui/components/progress';

  import { useUploadProgress } from './context.svelte';

  interface Props {
    value?: number;
    variant?: 'inline' | 'edge';
  }

  let { value, variant = 'inline' }: Props = $props();

  const progress = useUploadProgress();

  let resolvedValue = $derived(value ?? progress.overallProgress);
</script>

{#if variant === 'edge'}
  <Progress
    value={resolvedValue}
    variant="subtle"
    size="sm"
    class="absolute inset-x-0 bottom-0 h-1 w-full rounded-none"
  />
{:else}
  <Progress value={resolvedValue} variant="subtle" size="md" />
{/if}
