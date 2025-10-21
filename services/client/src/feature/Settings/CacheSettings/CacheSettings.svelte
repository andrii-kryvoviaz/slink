<script lang="ts">
  import { Notice } from '@slink/feature/Text';
  import { Button } from '@slink/ui/components/button';
  import { toast } from 'svelte-sonner';

  import Icon from '@iconify/svelte';

  import { ApiClient } from '@slink/api/Client';
  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { ClearCacheResponse } from '@slink/api/Resources/StorageResource';

  import { printErrorsAsToastMessage } from '@slink/utils/ui/printErrorsAsToastMessage';

  const {
    run: clearCache,
    isLoading,
    error,
    data,
  } = ReactiveState<ClearCacheResponse>(
    () => {
      return ApiClient.storage.clearCache();
    },
    { minExecutionTime: 500 },
  );

  let showConfirmation = $state(false);
  let lastClearedCount = $state<number | null>(null);

  const handleClearCache = async () => {
    await clearCache();

    if (!$error) {
      showConfirmation = false;
    }
  };

  $effect(() => {
    if ($data) {
      lastClearedCount = $data.cleared;
      toast.success($data.message);
    }
  });

  $effect(() => {
    if ($error) {
      printErrorsAsToastMessage($error);
      showConfirmation = false;
    }
  });
</script>

<div
  class="bg-white dark:bg-gray-900/50 border border-gray-200/50 dark:border-gray-700/30 rounded-2xl p-8 backdrop-blur-sm shadow-sm hover:shadow-md transition-all duration-200"
>
  <div class="mb-8 pb-6 border-b border-gray-200/60 dark:border-gray-700/40">
    <h2
      class="text-2xl font-light text-gray-900 dark:text-white mb-2 tracking-tight"
    >
      Cache Management
    </h2>
    <p class="text-sm text-gray-600 dark:text-gray-400">
      Manage cached image transformations and optimize storage usage
    </p>
  </div>

  <div class="space-y-6">
    <div
      class="flex items-start gap-4 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200/50 dark:border-gray-700/30"
    >
      <div
        class="flex-shrink-0 w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center"
      >
        <Icon
          icon="ph:database-duotone"
          class="w-5 h-5 text-blue-600 dark:text-blue-400"
        />
      </div>
      <div class="flex-1 min-w-0">
        <h3 class="text-base font-medium text-gray-900 dark:text-white mb-2">
          Image Cache
        </h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
          Cached files store transformed versions of images (resized, cropped,
          etc.) to improve performance. Clearing the cache will regenerate these
          files on next request.
        </p>

        {#if lastClearedCount !== null}
          <Notice size="sm" variant="success" class="mt-4">
            <Icon icon="ph:check-circle-duotone" class="w-4 h-4 inline mr-1" />
            Last operation cleared {lastClearedCount}
            {lastClearedCount === 1 ? 'file' : 'files'}
          </Notice>
        {/if}
      </div>
    </div>

    {#if !showConfirmation}
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-600 dark:text-gray-400">
          <Icon
            icon="ph:warning-duotone"
            class="inline w-4 h-4 mr-1 text-amber-500"
          />
          This action cannot be undone
        </div>
        <Button
          variant="outline"
          size="md"
          onclick={() => (showConfirmation = true)}
          disabled={$isLoading}
        >
          <Icon icon="ph:trash-duotone" class="w-4 h-4 mr-2" />
          Clear Cache
        </Button>
      </div>
    {:else}
      <div
        class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/30 rounded-xl"
      >
        <div class="flex items-start gap-3 mb-4">
          <Icon
            icon="ph:warning-circle-duotone"
            class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5"
          />
          <div class="flex-1">
            <h4
              class="text-sm font-medium text-amber-900 dark:text-amber-100 mb-1"
            >
              Confirm Cache Clearing
            </h4>
            <p class="text-sm text-amber-700 dark:text-amber-300">
              Are you sure you want to clear all cached images? This may
              temporarily impact performance until the cache is rebuilt.
            </p>
          </div>
        </div>
        <div class="flex items-center gap-3 justify-end">
          <Button
            variant="ghost"
            size="sm"
            onclick={() => (showConfirmation = false)}
            disabled={$isLoading}
          >
            Cancel
          </Button>
          <Button
            variant="destructive"
            size="sm"
            onclick={handleClearCache}
            disabled={$isLoading}
          >
            {#if $isLoading}
              <Icon
                icon="ph:circle-notch-duotone"
                class="w-4 h-4 mr-2 animate-spin"
              />
              Clearing...
            {:else}
              <Icon icon="ph:check-duotone" class="w-4 h-4 mr-2" />
              Yes, Clear Cache
            {/if}
          </Button>
        </div>
      </div>
    {/if}
  </div>
</div>
