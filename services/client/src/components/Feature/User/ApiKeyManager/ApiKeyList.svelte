<script lang="ts">
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  import type { ApiKeyResponse } from '@slink/api/Resources/ApiKeyResource';

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

<div class="space-y-4">
  {#if isLoading}
    <div class="flex items-center justify-center py-8">
      <Icon
        icon="lucide:loader-2"
        class="h-6 w-6 animate-spin text-slate-400"
      />
    </div>
  {:else if apiKeys.length === 0}
    <div class="text-center py-8">
      <Icon
        icon="ph:key"
        class="h-12 w-12 text-slate-300 dark:text-slate-600 mx-auto mb-3"
      />
      <p class="text-slate-500 dark:text-slate-400">No API keys created yet</p>
      <p class="text-sm text-slate-400 dark:text-slate-500">
        Create an API key to integrate with ShareX and other tools
      </p>
    </div>
  {:else}
    <div class="space-y-3">
      {#each apiKeys as apiKey (apiKey.id)}
        {@render apiKeyCard({
          apiKey,
          onShareXClick,
          onDeleteConfirm,
          isRevoking,
        })}
      {/each}
    </div>
  {/if}
</div>
