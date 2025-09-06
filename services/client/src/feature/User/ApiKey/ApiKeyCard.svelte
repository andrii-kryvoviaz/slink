<script lang="ts">
  import { Badge } from '@slink/feature/Text';
  import { ApiKeyDeletePopover } from '@slink/feature/User';
  import { Overlay } from '@slink/ui/components/popover';

  import { formatDate, formatExpiryDate } from '$lib/utils/date';
  import Icon from '@iconify/svelte';
  import { readable } from 'svelte/store';
  import { fade } from 'svelte/transition';

  import type { ApiKeyResponse } from '@slink/api/Resources/ApiKeyResource';

  interface Props {
    apiKey: ApiKeyResponse;
    isRevoking: boolean;
    onDeleteConfirm: (apiKey: ApiKeyResponse) => void;
  }

  let { apiKey, isRevoking, onDeleteConfirm }: Props = $props();

  let popoverOpen = $state(false);
</script>

<div
  class="relative border border-slate-200 dark:border-slate-700 rounded-xl p-4 bg-white/60 dark:bg-slate-800/60 backdrop-blur-sm shadow-sm hover:shadow-md transition-all duration-200 group"
  transition:fade={{ duration: 200 }}
>
  <div class="pr-8">
    <div class="flex items-center gap-2">
      <h5 class="font-semibold text-slate-900 dark:text-slate-100">
        {apiKey.name}
      </h5>
      {#if apiKey.isExpired}
        <Badge variant="destructive" size="xs">Expired</Badge>
      {:else if apiKey.expiresAt}
        <Badge variant="amber" size="xs">Active</Badge>
      {:else}
        <Badge variant="success" size="xs">Permanent</Badge>
      {/if}
    </div>
    <div
      class="flex items-center gap-4 mt-2 text-sm text-slate-500 dark:text-slate-400"
    >
      <span class="flex items-center gap-1">
        <Icon icon="lucide:calendar" class="h-3.5 w-3.5" />
        Created {formatDate(apiKey.createdAt)}
      </span>
      {#if apiKey.expiresAt}
        <span class="flex items-center gap-1">
          <Icon icon="lucide:clock" class="h-3.5 w-3.5" />
          {formatExpiryDate(apiKey.expiresAt)}
        </span>
      {/if}
      {#if apiKey.lastUsedAt}
        <span class="flex items-center gap-1">
          <Icon icon="lucide:activity" class="h-3.5 w-3.5" />
          Last used {formatDate(apiKey.lastUsedAt)}
        </span>
      {/if}
    </div>
  </div>

  <div class="absolute top-3 right-3">
    <Overlay
      bind:open={popoverOpen}
      variant="floating"
      contentProps={{ align: 'end' }}
    >
      {#snippet trigger()}
        <button
          type="button"
          title="Revoke API Key"
          class="opacity-0 group-hover:opacity-100 w-8 h-8 flex items-center justify-center rounded-lg bg-white/80 dark:bg-gray-700/80 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all duration-200 focus:opacity-100 focus:outline-none focus:ring-2 focus:ring-red-500/20 shadow-sm"
          disabled={isRevoking}
        >
          <Icon icon="lucide:x" class="h-4 w-4" />
        </button>
      {/snippet}

      <ApiKeyDeletePopover
        {apiKey}
        loading={readable(isRevoking)}
        confirm={onDeleteConfirm}
        onCancel={() => (popoverOpen = false)}
      />
    </Overlay>
  </div>
</div>
