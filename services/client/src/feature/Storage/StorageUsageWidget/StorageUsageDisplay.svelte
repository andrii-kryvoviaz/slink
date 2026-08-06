<script lang="ts">
  import { bytesToSize } from '$lib/utils/bytesConverter';
  import Icon from '@iconify/svelte';

  import type { StorageUsageResponse } from '@slink/api/Resources/StorageResource';

  interface Props {
    data?: StorageUsageResponse | null;
    isLoading?: boolean;
    error?: string | null;
  }

  let { data, isLoading = false, error = null }: Props = $props();

  function getProviderDisplayName(provider: string) {
    switch (provider) {
      case 'local':
        return 'Local Storage';
      case 's3':
        return 'Amazon S3';
      case 'smb':
        return 'SMB Share';
      default:
        return provider;
    }
  }
</script>

<div
  class="bg-sidebar-accent/10 border border-sidebar-border rounded-lg p-4 space-y-3"
>
  {#if error}
    <div class="flex items-center gap-2 text-danger text-sm">
      <Icon icon="heroicons:exclamation-triangle" class="h-4 w-4" />
      <span>Failed to load storage usage</span>
    </div>
  {:else if data}
    {@const totalBytes = data.usedBytes + data.cacheBytes}
    {@const hasBreakdown = data.usedBytes > 0 && data.cacheBytes > 0}

    <div>
      <p
        class="text-[10.5px] font-semibold uppercase tracking-wider text-sidebar-foreground/40 truncate"
      >
        {getProviderDisplayName(data.provider)}
      </p>
      <p
        class="mt-1 text-[22px] font-semibold tracking-tight tabular-nums text-sidebar-foreground"
      >
        {bytesToSize(totalBytes)}
      </p>
    </div>

    {#if totalBytes > 0}
      <div class="flex h-1.5 w-full gap-0.5">
        {#if hasBreakdown}
          {@const imagesPercent = (data.usedBytes / totalBytes) * 100}
          {@const cachePercent = (data.cacheBytes / totalBytes) * 100}
          <div
            class="min-w-1.5 rounded-full bg-info-fill transition-all duration-300"
            style="width: {imagesPercent}%"
          ></div>
          <div
            class="min-w-1.5 rounded-full bg-decor-violet-strong transition-all duration-300"
            style="width: {cachePercent}%"
          ></div>
        {:else if data.usedBytes > 0}
          <div
            class="w-full rounded-full bg-info-fill transition-all duration-300"
          ></div>
        {:else}
          <div
            class="w-full rounded-full bg-decor-violet-strong transition-all duration-300"
          ></div>
        {/if}
      </div>
    {/if}

    <div class="space-y-2">
      <div class="flex items-center justify-between gap-x-2 text-xs">
        <div class="flex items-center gap-1.5 min-w-0">
          <div class="w-2 h-2 rounded-full bg-info-fill shrink-0"></div>
          <span class="text-sidebar-foreground/60 truncate">Images</span>
          <span class="text-sidebar-foreground/40 tabular-nums">
            {data.fileCount.toLocaleString()}
          </span>
        </div>
        <span
          class="font-medium text-sidebar-foreground tabular-nums whitespace-nowrap"
        >
          {bytesToSize(data.usedBytes)}
        </span>
      </div>
      {#if data.cacheBytes > 0}
        <div class="flex items-center justify-between gap-x-2 text-xs">
          <div class="flex items-center gap-1.5 min-w-0">
            <div
              class="w-2 h-2 rounded-full bg-decor-violet-strong shrink-0"
            ></div>
            <span class="text-sidebar-foreground/60 truncate">Cache</span>
            <span class="text-sidebar-foreground/40 tabular-nums">
              {data.cacheFileCount.toLocaleString()}
            </span>
          </div>
          <span
            class="font-medium text-sidebar-foreground tabular-nums whitespace-nowrap"
          >
            {bytesToSize(data.cacheBytes)}
          </span>
        </div>
      {/if}
    </div>
  {:else if isLoading}
    <div class="space-y-3 animate-pulse">
      <div class="space-y-2">
        <div class="h-2.5 bg-sidebar-accent/20 rounded w-14"></div>
        <div class="h-6 bg-sidebar-accent/20 rounded w-24"></div>
      </div>
      <div class="h-1.5 bg-sidebar-accent/20 rounded-full w-full"></div>
      <div class="space-y-2">
        <div class="flex items-center justify-between">
          <div class="h-3 bg-sidebar-accent/20 rounded w-12"></div>
          <div class="h-3 bg-sidebar-accent/20 rounded w-28"></div>
        </div>
        <div class="flex items-center justify-between">
          <div class="h-3 bg-sidebar-accent/20 rounded w-12"></div>
          <div class="h-3 bg-sidebar-accent/20 rounded w-28"></div>
        </div>
      </div>
    </div>
  {:else}
    <div class="text-sm text-sidebar-foreground/60 text-center py-2">
      No storage data available
    </div>
  {/if}
</div>
