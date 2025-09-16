<script lang="ts">
  import { Button } from '@slink/ui/components/button';
  import { Progress } from '@slink/ui/components/progress';
  import { ScrollArea } from '@slink/ui/components/scroll-area';

  import { bytesToSize } from '$lib/utils/bytesConverter';
  import { className as cn } from '$lib/utils/ui/className';
  import Icon from '@iconify/svelte';
  import { fade, fly } from 'svelte/transition';

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

  const getStatusIcon = (status: UploadItem['status']) => {
    switch (status) {
      case 'completed':
        return 'ph:check-circle-fill';
      case 'error':
        return 'ph:x-circle-fill';
      case 'uploading':
        return 'ph:spinner';
      default:
        return 'ph:clock';
    }
  };

  const getStatusColor = (status: UploadItem['status']) => {
    switch (status) {
      case 'completed':
        return 'text-green-500';
      case 'error':
        return 'text-red-500';
      case 'uploading':
        return 'text-blue-500';
      default:
        return 'text-gray-400';
    }
  };
</script>

<div
  class={cn(
    'space-y-6 p-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700',
    className,
  )}
  in:fade={{ duration: 300 }}
>
  <div class="flex items-center justify-between">
    <div class="space-y-1">
      <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
        {#if isCompleted}
          Upload Complete
        {:else}
          Uploading Files
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
          variant="glass"
          size="sm"
          rounded="full"
          class="bg-red-100/80 hover:bg-red-200/80 dark:bg-red-800/40 dark:hover:bg-red-700/60 border-red-200 hover:border-red-300 dark:border-red-600 dark:hover:border-red-500 text-red-700 hover:text-red-800 dark:text-red-200 dark:hover:text-red-100"
          onclick={onRetryAll}
        >
          <Icon icon="ph:arrow-clockwise" class="w-4 h-4 mr-2" />
          Retry Failed Uploads
        </Button>
      {/if}

      {#if onCancel && !isCompleted}
        <Button
          variant="glass"
          size="sm"
          rounded="full"
          class="bg-gray-100/80 hover:bg-gray-200/80 dark:bg-gray-700/80 dark:hover:bg-gray-600/80 border-gray-200 hover:border-gray-300 dark:border-gray-600 dark:hover:border-gray-500 text-gray-700 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-200 transition-all duration-200"
          onclick={onCancel}
        >
          <Icon icon="ph:x" class="w-4 h-4 mr-2" />
          Cancel
        </Button>
      {:else if isCompleted && onGoBack}
        <Button
          variant="glass"
          size="sm"
          rounded="full"
          class="bg-gray-100/80 hover:bg-gray-200/80 dark:bg-gray-700/80 dark:hover:bg-gray-600/80 border-gray-200 hover:border-gray-300 dark:border-gray-600 dark:hover:border-gray-500 text-gray-700 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-200 transition-all duration-200"
          onclick={onGoBack}
        >
          <Icon icon="ph:check" class="w-4 h-4 mr-2" />
          Done
        </Button>
      {/if}
    </div>
  </div>

  <div class="space-y-2">
    <div class="flex items-center justify-between text-sm">
      <span class="text-slate-600 dark:text-slate-300">Overall Progress</span>
      <span class="font-medium text-slate-900 dark:text-slate-100">
        {overallProgress.toFixed(0)}%
      </span>
    </div>

    <Progress
      value={overallProgress}
      variant={hasErrors && isCompleted
        ? 'error'
        : isCompleted
          ? 'success'
          : 'default'}
      size="md"
    />
  </div>

  <ScrollArea class="max-h-64 overflow-y-auto" orientation="vertical">
    <div class="space-y-3">
      {#each uploads as item, index (item.id)}
        <div
          class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200/50 dark:border-slate-700/50"
          in:fly={{ duration: 200, delay: index * 50, y: 10 }}
        >
          <div class="flex-shrink-0">
            <Icon
              icon={getStatusIcon(item.status)}
              class={cn('w-5 h-5', getStatusColor(item.status), {
                'animate-spin': item.status === 'uploading',
              })}
            />
          </div>

          <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between mb-1">
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
              <Progress value={item.progress} size="sm" variant="default" />
            {:else if item.status === 'error'}
              <p
                class="text-xs text-red-500 dark:text-red-400 mt-1 break-words"
              >
                {item.error || 'Upload failed'}
              </p>
            {:else if item.status === 'completed'}
              <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                Upload successful
              </p>
            {/if}
          </div>
        </div>
      {/each}
    </div>
  </ScrollArea>
</div>
