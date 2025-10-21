<script lang="ts">
  import Badge from '@slink/feature/Text/Badge/Badge.svelte';

  import { bytesToSize } from '$lib/utils/bytesConverter';
  import Icon from '@iconify/svelte';

  import type { StorageUsageResponse } from '@slink/api/Resources/StorageResource';

  interface Props {
    data?: StorageUsageResponse | null;
    isLoading?: boolean;
    error?: string | null;
    isLiveUpdatesEnabled?: boolean;
    isSSEConnected?: boolean;
  }

  let { data, isLoading = false, error = null }: Props = $props();

  function getProviderIcon(provider: string) {
    switch (provider) {
      case 'local':
        return 'heroicons:server';
      case 's3':
        return 'heroicons:cloud';
      case 'smb':
        return 'heroicons:server-stack';
      default:
        return 'heroicons:server';
    }
  }

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
  class="bg-sidebar-card border border-sidebar-border rounded-lg p-4 space-y-3"
>
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-2">
      <Icon
        icon={data ? getProviderIcon(data.provider) : 'heroicons:server'}
        class="h-4 w-4 text-sidebar-foreground/70"
      />
      <h3 class="text-sm font-medium text-sidebar-foreground">Storage Usage</h3>
    </div>
  </div>

  <div class="space-y-3">
    {#if error}
      <div class="flex items-center gap-2 text-red-500 text-sm">
        <Icon icon="heroicons:exclamation-triangle" class="h-4 w-4" />
        <span>Failed to load storage usage</span>
      </div>
    {:else if data}
      <div class="space-y-3">
        <div class="flex items-center justify-between text-xs">
          <span class="text-sidebar-foreground/60">Provider</span>
          <Badge size="xs" variant="blue">
            {getProviderDisplayName(data.provider)}
          </Badge>
        </div>

        <div class="space-y-3">
          <div>
            <div class="flex items-center justify-between text-xs mb-2">
              <span class="text-sidebar-foreground/60">Total Usage</span>
              <span class="font-medium text-sidebar-foreground">
                {bytesToSize(data.usedBytes + data.cacheBytes)}
              </span>
            </div>

            <div
              class="relative h-1 w-full bg-sidebar-accent/20 rounded-full overflow-hidden"
            >
              {#if data.usedBytes > 0 || data.cacheBytes > 0}
                {@const totalBytes = data.usedBytes + data.cacheBytes}
                {@const imagesPercent = (data.usedBytes / totalBytes) * 100}
                {@const cachePercent = (data.cacheBytes / totalBytes) * 100}

                <div class="absolute inset-0 flex">
                  <div
                    class="bg-blue-500 transition-all duration-300"
                    style="width: {imagesPercent}%"
                  ></div>
                  <div
                    class="bg-purple-500 transition-all duration-300"
                    style="width: {cachePercent}%"
                  ></div>
                </div>
              {/if}
            </div>

            <div class="space-y-2 mt-3">
              <div class="flex items-center justify-between text-xs">
                <div class="flex items-center gap-1.5">
                  <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                  <span class="text-sidebar-foreground/60">Images</span>
                </div>
                <div class="flex items-center gap-2">
                  <span class="font-medium text-sidebar-foreground"
                    >{bytesToSize(data.usedBytes)}</span
                  >
                  <span class="text-sidebar-foreground/40">·</span>
                  <span class="text-sidebar-foreground/60 tabular-nums"
                    >{data.fileCount.toLocaleString()} files</span
                  >
                </div>
              </div>
              {#if data.cacheBytes > 0}
                <div class="flex items-center justify-between text-xs">
                  <div class="flex items-center gap-1.5">
                    <div class="w-2 h-2 rounded-full bg-purple-500"></div>
                    <span class="text-sidebar-foreground/60">Cache</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <span class="font-medium text-sidebar-foreground"
                      >{bytesToSize(data.cacheBytes)}</span
                    >
                    <span class="text-sidebar-foreground/40">·</span>
                    <span class="text-sidebar-foreground/60 tabular-nums"
                      >{data.cacheFileCount.toLocaleString()} files</span
                    >
                  </div>
                </div>
              {/if}
            </div>
          </div>
        </div>
      </div>
    {:else if isLoading}
      <div class="space-y-3 animate-pulse">
        <div class="h-4 bg-sidebar-accent/20 rounded"></div>
        <div class="h-2 bg-sidebar-accent/20 rounded"></div>
        <div class="h-4 bg-sidebar-accent/20 rounded w-3/4"></div>
      </div>
    {:else}
      <div class="text-sm text-sidebar-foreground/60 text-center py-2">
        No storage data available
      </div>
    {/if}
  </div>
</div>
