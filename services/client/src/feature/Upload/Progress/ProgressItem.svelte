<script lang="ts">
  import { Loader } from '@slink/feature/Layout';
  import { Progress } from '@slink/ui/components/progress';

  import { bytesToSize } from '$lib/utils/bytesConverter';
  import { className as cn } from '$lib/utils/ui/className';
  import Icon from '@iconify/svelte';

  import type { UploadItem } from '@slink/lib/services/upload.service';

  import { UploadProgressStatusIconTheme } from './Progress.theme';

  interface Props {
    item: UploadItem;
    class?: string;
  }

  let { item, class: className }: Props = $props();
</script>

<div
  class={cn(
    'flex items-center gap-3 p-3 rounded-lg bg-muted/50 border border-border/50 backdrop-blur-sm',
    className,
  )}
>
  <div class="flex-shrink-0">
    {#if item.status === 'uploading'}
      <Loader variant="minimal" size="xs" />
    {:else if item.status === 'completed'}
      <Icon
        icon="ph:check-circle-fill"
        class={UploadProgressStatusIconTheme({ status: 'completed' })}
      />
    {:else if item.status === 'error'}
      <Icon
        icon="ph:x-circle-fill"
        class={UploadProgressStatusIconTheme({ status: 'error' })}
      />
    {:else if item.status === 'cancelled'}
      <Icon
        icon="ph:prohibit"
        class={UploadProgressStatusIconTheme({ status: 'cancelled' })}
      />
    {:else}
      <Icon
        icon="ph:clock"
        class={UploadProgressStatusIconTheme({ status: 'pending' })}
      />
    {/if}
  </div>

  <div class="flex-1 min-w-0">
    <div class="flex items-center justify-between">
      <p class="text-sm font-medium text-foreground truncate">
        {item.file.name}
      </p>
      <span class="text-xs text-muted-foreground ml-2">
        {bytesToSize(item.file.size)}
      </span>
    </div>

    {#if item.status === 'uploading'}
      <div class="mt-1">
        <Progress value={item.progress} size="sm" variant="subtle" />
      </div>
    {:else if item.status === 'error'}
      <p class="text-xs text-danger/80 mt-1 break-words">
        {#if item.error}{item.error}{:else}Upload failed{/if}
      </p>
    {:else if item.status === 'cancelled'}
      <p class="text-xs text-foreground-subtle mt-1 break-words">
        {#if item.error}{item.error}{:else}Upload cancelled{/if}
      </p>
    {/if}
  </div>
</div>
