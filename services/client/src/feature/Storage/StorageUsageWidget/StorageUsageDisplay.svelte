<script lang="ts">
  import Icon from '@iconify/svelte';
  import type { StorageUsageResponse } from '@slink/api/Resources/StorageResource';
  import { bytesToSize } from '$lib/utils/bytesConverter';
  import Badge from '@slink/feature/Text/Badge/Badge.svelte';

  interface Props {
    data?: StorageUsageResponse | null;
    isLoading?: boolean;
    error?: string | null;
    isLiveUpdatesEnabled?: boolean;
    isSSEConnected?: boolean;
  }

  let {
    data,
    isLoading = false,
    error = null,
  }: Props = $props();

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

  function getUsagePercentage(): number {
    if (!data?.totalBytes || !data?.usedBytes) return 0;
    return (data.usedBytes / data.totalBytes) * 100;
  }

  function getUsageColor(): string {
    const percentage = getUsagePercentage();
    if (percentage >= 90) return 'text-red-500';
    if (percentage >= 75) return 'text-orange-500';
    return 'text-sidebar-foreground';
  }

  function getProgressColor(): string {
    const percentage = getUsagePercentage();
    if (percentage >= 90) return 'bg-red-500';
    if (percentage >= 75) return 'bg-orange-500';
    return 'bg-blue-500';
  }
</script>

<div class="bg-sidebar-card border border-sidebar-border rounded-lg p-4 space-y-3">
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

        <div class="space-y-2">
          <div class="flex items-center justify-between text-xs">
            <span class="text-sidebar-foreground/60">Used Storage</span>
            <span class={getUsageColor()}>
              {bytesToSize(data.usedBytes)}
            </span>
          </div>

          {#if data.totalBytes}
            <div class="space-y-1">
              <div class="w-full bg-sidebar-accent/20 rounded-full h-1.5">
                <div
                  class="h-1.5 rounded-full transition-all duration-300 {getProgressColor()}"
                  style="width: {getUsagePercentage()}%"
                ></div>
              </div>
              <div
                class="flex justify-between text-xs text-sidebar-foreground/60"
              >
                <span>{getUsagePercentage().toFixed(1)}% used</span>
                <span>{bytesToSize(data.totalBytes)} total</span>
              </div>
            </div>
          {/if}

          <div class="flex items-center justify-between text-xs">
            <span class="text-sidebar-foreground/60">Files</span>
            <span class="font-medium text-sidebar-foreground"
              >{data.fileCount.toLocaleString()}</span
            >
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