<script lang="ts">
  import type { Snippet } from 'svelte';

  import { className as cn } from '$lib/utils/ui/className';
  import { cubicOut } from 'svelte/easing';
  import { Tween } from 'svelte/motion';

  import { UploadProgressNumberTheme } from './Progress.theme';
  import { useUploadProgress } from './context.svelte';

  interface Props {
    value?: number;
    size?: 'sm' | 'md' | 'lg';
    animated?: boolean;
    shimmer?: boolean;
    caption?: Snippet;
    class?: string;
  }

  let {
    value,
    size = 'sm',
    animated = false,
    shimmer = false,
    caption,
    class: className,
  }: Props = $props();

  const progress = useUploadProgress();

  let resolvedValue = $derived(value ?? progress.overallProgress);
  let percentage = $derived(Math.min(Math.max(resolvedValue, 0), 100));

  const tween = Tween.of(() => percentage, {
    duration: 400,
    easing: cubicOut,
  });
</script>

<div class={cn('flex flex-col items-center gap-1', className)}>
  <span class={UploadProgressNumberTheme({ size, animated, shimmer })}>
    {#if animated}
      {Math.round(tween.current)}%
    {:else}
      {percentage.toFixed(0)}%
    {/if}
  </span>
  {#if caption}
    <span
      class="text-[10px] uppercase tracking-widest text-slate-400 dark:text-slate-500"
    >
      {@render caption()}
    </span>
  {/if}
</div>
