<script lang="ts">
  import { Loader } from '@slink/feature/Layout';
  import { Button } from '@slink/ui/components/button';
  import { Progress } from '@slink/ui/components/progress';
  import { cva } from 'class-variance-authority';

  import { bytesToSize } from '$lib/utils/bytesConverter';
  import { className as cn } from '$lib/utils/ui/className';
  import Icon from '@iconify/svelte';
  import { fade, fly } from 'svelte/transition';

  const statusIconVariants = cva('w-5 h-5', {
    variants: {
      status: {
        completed: 'text-slate-600 dark:text-slate-300',
        error: 'text-red-500 dark:text-red-400',
        pending: 'text-slate-300 dark:text-slate-600',
      },
    },
  });

  interface UploadItem {
    file: File;
    id: string;
    status: 'pending' | 'uploading' | 'completed' | 'error';
    progress: number;
    result?: any;
    error?: string;
    errorDetails?: Error;
  }

  interface Props {
    uploads: UploadItem[];
    onCancel?: () => void;
    onRetryAll?: () => void;
    onGoBack?: () => void;
    class?: string;
  }

  let {
    uploads,
    onCancel,
    onRetryAll,
    onGoBack,
    class: className,
  }: Props = $props();

  let completedUploads = $derived(
    uploads.filter((item) => item.status === 'completed').length,
  );
  let failedUploads = $derived(
    uploads.filter((item) => item.status === 'error').length,
  );
  let totalUploads = $derived(uploads.length);
  let overallProgress = $derived(
    totalUploads > 0 ? (completedUploads / totalUploads) * 100 : 0,
  );
  let isCompleted = $derived(completedUploads + failedUploads === totalUploads);
  let hasErrors = $derived(failedUploads > 0);
</script>

<div
  class={cn(
    'space-y-6 p-6 bg-white/80 dark:bg-slate-800/50 border border-slate-200/70 dark:border-slate-700/50 shadow-lg shadow-slate-500/5 dark:shadow-black/10 backdrop-blur-sm rounded-xl',
    className,
  )}
  in:fade={{ duration: 300 }}
>
  <div in:fade={{ duration: 400, delay: 50 }}>
    <div class="flex items-center justify-between">
      <div class="space-y-1">
        <h3
          class="text-xl sm:text-2xl font-semibold bg-gradient-to-r from-slate-700 to-slate-900 dark:from-slate-200 dark:to-slate-400 bg-clip-text text-transparent"
        >
          {#if isCompleted}
            Complete
          {:else}
            Uploading
          {/if}
        </h3>
        <p class="text-sm text-slate-500 dark:text-slate-400">
          {completedUploads} of {totalUploads} files uploaded
          {#if hasErrors}
            • {failedUploads} failed
          {/if}
        </p>
      </div>

      <div class="flex items-center gap-3">
        {#if isCompleted && hasErrors && onRetryAll}
          <Button
            variant="soft-red"
            size="sm"
            rounded="full"
            onclick={onRetryAll}
          >
            <Icon icon="ph:arrow-clockwise" class="w-4 h-4 mr-2" />
            Retry Failed
          </Button>
        {/if}

        {#if onCancel && !isCompleted}
          <Button variant="glass" size="sm" rounded="full" onclick={onCancel}>
            <Icon icon="ph:x" class="w-4 h-4 mr-2" />
            Cancel
          </Button>
        {:else if isCompleted && onGoBack}
          <Button variant="glass" size="sm" rounded="full" onclick={onGoBack}>
            <Icon icon="ph:check" class="w-4 h-4 mr-2" />
            Done
          </Button>
        {/if}
      </div>
    </div>
  </div>

  <div class="space-y-3" in:fade={{ duration: 400, delay: 100 }}>
    <div class="flex flex-col items-center gap-1">
      <span
        class="text-3xl font-light tracking-tight text-slate-900 dark:text-white"
      >
        {overallProgress.toFixed(0)}%
      </span>
      <span
        class="text-[10px] uppercase tracking-widest text-slate-400 dark:text-slate-500"
      >
        Overall Progress
      </span>
    </div>

    <Progress value={overallProgress} variant="subtle" size="md" />
  </div>

  <div class="relative">
    <div class="max-h-64 overflow-y-auto space-y-2 pr-1">
      {#each uploads as item, index (item.id)}
        <div
          class="flex items-center gap-3 p-3 rounded-lg bg-slate-50/80 dark:bg-slate-700/30 border border-slate-200/50 dark:border-slate-600/30 backdrop-blur-sm"
          in:fly={{ duration: 250, delay: 150 + index * 40, y: 8 }}
        >
          <div class="flex-shrink-0">
            {#if item.status === 'uploading'}
              <Loader variant="minimal" size="xs" />
            {:else if item.status === 'completed'}
              <Icon
                icon="ph:check-circle-fill"
                class={statusIconVariants({ status: 'completed' })}
              />
            {:else if item.status === 'error'}
              <Icon
                icon="ph:x-circle-fill"
                class={statusIconVariants({ status: 'error' })}
              />
            {:else}
              <Icon
                icon="ph:clock"
                class={statusIconVariants({ status: 'pending' })}
              />
            {/if}
          </div>

          <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between">
              <p
                class="text-sm font-medium text-slate-900 dark:text-slate-100 truncate"
              >
                {item.file.name}
              </p>
              <span class="text-xs text-slate-500 dark:text-slate-400 ml-2">
                {bytesToSize(item.file.size)}
              </span>
            </div>

            {#if item.status === 'uploading'}
              <div class="mt-1">
                <Progress value={item.progress} size="sm" variant="subtle" />
              </div>
            {:else if item.status === 'error'}
              <p
                class="text-xs text-red-500/80 dark:text-red-400/80 mt-1 break-words"
              >
                {item.error || 'Upload failed'}
              </p>
            {/if}
          </div>
        </div>
      {/each}
    </div>
  </div>
</div>
