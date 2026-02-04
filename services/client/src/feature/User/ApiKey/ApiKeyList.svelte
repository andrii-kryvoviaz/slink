<script lang="ts">
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  import type { ApiKeyResponse } from '@slink/api/Resources/ApiKeyResource';

  import ApiKeyListSkeleton from './ApiKeyListSkeleton.svelte';

  interface Props {
    apiKeys: ApiKeyResponse[];
    isLoading: boolean;
    onShareXClick: (apiKey: ApiKeyResponse) => void;
    onDeleteConfirm: (apiKey: ApiKeyResponse) => void;
    isRevoking: boolean;
    apiKeyCard: Snippet<
      [
        {
          apiKey: ApiKeyResponse;
          onShareXClick: (apiKey: ApiKeyResponse) => void;
          onDeleteConfirm: (apiKey: ApiKeyResponse) => void;
          isRevoking: boolean;
        },
      ]
    >;
  }

  let {
    apiKeys,
    isLoading,
    onShareXClick,
    onDeleteConfirm,
    isRevoking,
    apiKeyCard,
  }: Props = $props();
</script>

<div class="space-y-3">
  {#if isLoading && apiKeys.length === 0}
    <ApiKeyListSkeleton />
  {:else if !isLoading && apiKeys.length === 0}
    <div class="flex flex-col items-center justify-center py-16 text-center">
      <div
        class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-800/80 flex items-center justify-center mb-4"
      >
        <Icon icon="ph:key" class="h-8 w-8 text-gray-400 dark:text-gray-500" />
      </div>
      <p class="text-gray-900 dark:text-white font-medium">No API keys yet</p>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-xs">
        Create an API key to integrate with ShareX and other tools
      </p>
    </div>
  {:else}
    {#each apiKeys as apiKey (apiKey.id)}
      {@render apiKeyCard({
        apiKey,
        onShareXClick,
        onDeleteConfirm,
        isRevoking,
      })}
    {/each}
  {/if}
</div>
