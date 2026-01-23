<script lang="ts">
  import { ApiKeyDeletePopover } from '@slink/feature/User';
  import { Overlay } from '@slink/ui/components/popover';

  import { formatDate, formatExpiryDate } from '$lib/utils/date';
  import Icon from '@iconify/svelte';
  import { readable } from 'svelte/store';
  import { fade } from 'svelte/transition';

  import type { ApiKeyResponse } from '@slink/api/Resources/ApiKeyResource';

  import {
    type ApiKeyStatus,
    apiKeyIconContainerVariants,
    apiKeyIconVariants,
    statusLabels,
  } from './ApiKeyCard.theme';

  interface Props {
    apiKey: ApiKeyResponse;
    isRevoking: boolean;
    onDeleteConfirm: (apiKey: ApiKeyResponse) => void;
  }

  let { apiKey, isRevoking, onDeleteConfirm }: Props = $props();

  let popoverOpen = $state(false);

  const status: ApiKeyStatus = $derived(
    apiKey.isExpired ? 'expired' : apiKey.expiresAt ? 'active' : 'permanent',
  );
</script>

<div
  class="relative rounded-lg border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-gray-900/60 transition-all duration-200 hover:border-gray-300 dark:hover:border-gray-700/80 group"
  transition:fade={{ duration: 200 }}
>
  <div class="flex items-center gap-4">
    <div class={apiKeyIconContainerVariants({ status })}>
      <Icon icon="ph:key" class={apiKeyIconVariants({ status })} />
    </div>

    <div class="min-w-0 flex-1">
      <div class="flex items-center gap-2">
        <h5 class="font-medium text-gray-900 dark:text-white truncate text-sm">
          {apiKey.name}
        </h5>
        <span class="text-xs text-gray-400 dark:text-gray-500">
          · {statusLabels[status]}
        </span>
      </div>
      <div
        class="flex items-center gap-2 mt-1 text-xs text-gray-500 dark:text-gray-400"
      >
        <span class="flex items-center gap-1">
          <Icon icon="lucide:calendar" class="h-3 w-3" />
          {formatDate(apiKey.createdAt)}
        </span>
        {#if apiKey.expiresAt}
          <span class="text-gray-300 dark:text-gray-600">·</span>
          <span class="flex items-center gap-1">
            <Icon icon="lucide:clock" class="h-3 w-3" />
            {formatExpiryDate(apiKey.expiresAt)}
          </span>
        {/if}
        {#if apiKey.lastUsedAt}
          <span class="text-gray-300 dark:text-gray-600">·</span>
          <span class="flex items-center gap-1">
            <Icon icon="lucide:activity" class="h-3 w-3" />
            {formatDate(apiKey.lastUsedAt)}
          </span>
        {/if}
      </div>
    </div>

    <Overlay
      bind:open={popoverOpen}
      variant="floating"
      contentProps={{ align: 'end' }}
    >
      {#snippet trigger()}
        <button
          type="button"
          title="Revoke API Key"
          class="opacity-0 group-hover:opacity-100 w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all duration-200 focus:opacity-100 focus:outline-none"
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
