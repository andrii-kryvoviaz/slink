<script lang="ts">
  import Icon from '@iconify/svelte';
  import { type Readable, readable } from 'svelte/store';

  import type { ApiKeyResponse } from '@slink/api/Resources/ApiKeyResource';

  import { Button } from '@slink/components/UI/Action';
  import { Loader } from '@slink/components/UI/Loader';

  interface Props {
    apiKey: ApiKeyResponse;
    loading?: Readable<boolean>;
    confirm: (apiKey: ApiKeyResponse) => void;
    onCancel?: () => void;
  }

  let {
    apiKey,
    loading = readable(false),
    confirm,
    onCancel,
  }: Props = $props();

  const handleConfirm = () => {
    confirm(apiKey);
  };
</script>

<div class="w-xs max-w-screen space-y-4 relative">
  {#if $loading}
    <div class="absolute top-2 right-2 z-10">
      <Loader variant="minimal" size="xs" />
    </div>
  {/if}

  <div class="flex items-center gap-3">
    <div
      class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 border border-red-200/40 dark:border-red-800/30 shadow-sm flex-shrink-0"
    >
      <Icon
        icon="lucide:trash-2"
        class="h-5 w-5 text-red-600 dark:text-red-400"
      />
    </div>
    <div>
      <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
        Revoke API Key
      </h3>
      <p class="text-xs text-gray-500 dark:text-gray-400">
        This action cannot be undone
      </p>
    </div>
  </div>

  <div
    class="bg-gray-50/80 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-200/50 dark:border-gray-700/30"
  >
    <div class="flex items-center gap-3">
      <div
        class="flex h-8 w-12 items-center justify-center rounded-lg bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 shadow-sm"
      >
        <Icon icon="ph:key" class="h-4 w-4 text-gray-600 dark:text-gray-400" />
      </div>
      <div>
        <span class="text-sm font-medium text-gray-900 dark:text-white">
          {apiKey.name}
        </span>
        <p class="text-xs text-gray-500 dark:text-gray-400">
          This API key will be permanently revoked and cannot be recovered
        </p>
      </div>
    </div>
  </div>

  <div class="flex gap-3 pt-2">
    <Button
      variant="glass"
      size="md"
      class="flex-1"
      disabled={$loading}
      onclick={onCancel}
    >
      Cancel
    </Button>
    <Button
      variant="danger"
      size="md"
      onclick={handleConfirm}
      class="flex-1 font-medium shadow-lg hover:shadow-xl transition-all duration-200"
      disabled={$loading}
    >
      {#if $loading}
        <Icon icon="lucide:loader-2" class="h-4 w-4 mr-2 animate-spin" />
      {:else}
        <Icon icon="lucide:trash-2" class="h-4 w-4 mr-2" />
      {/if}
      Revoke Key
    </Button>
  </div>
</div>
