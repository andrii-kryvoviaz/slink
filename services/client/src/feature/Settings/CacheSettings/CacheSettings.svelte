<script lang="ts">
  import { ApiClient } from '@slink/api';
  import { Loader } from '@slink/feature/Layout';
  import { SettingItem } from '@slink/feature/Settings';
  import { Button } from '@slink/ui/components/button';

  import { t } from '$lib/i18n';
  import Icon from '@iconify/svelte';

  import { ReactiveState } from '@slink/api/ReactiveState';
  import type { ClearCacheResponse } from '@slink/api/Resources/StorageResource';

  import { printErrorsAsToastMessage } from '@slink/utils/ui/printErrorsAsToastMessage';
  import { toast } from '@slink/utils/ui/toast-sonner.svelte';

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

  const handleClearCache = async () => {
    await clearCache();

    if (!$error) {
      showConfirmation = false;
    }
  };

  $effect(() => {
    if ($data) {
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

<section class="space-y-1">
  <div class="flex items-center justify-between gap-4 pb-3">
    <div>
      <h2
        class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
      >
        {$t('settings.cache.title')}
      </h2>
    </div>
  </div>

  <div
    class="divide-y divide-gray-100 dark:divide-gray-800 rounded-xl bg-gray-50/50 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-800 overflow-hidden"
  >
    <SettingItem>
      {#snippet label()}
        {$t('settings.cache.image_cache')}
      {/snippet}
      {#snippet hint()}
        {$t('settings.cache.image_cache_hint')}
      {/snippet}
      <div class="flex items-center gap-3">
        {#if showConfirmation}
          <Button
            variant="ghost"
            size="sm"
            rounded="full"
            onclick={() => (showConfirmation = false)}
            disabled={$isLoading}
          >
            {$t('settings.common.cancel')}
          </Button>
          <Button
            variant="soft-red"
            size="sm"
            rounded="full"
            onclick={handleClearCache}
            disabled={$isLoading}
          >
            {#if $isLoading}
              <Icon
                icon="ph:circle-notch-duotone"
                class="w-4 h-4 mr-2 animate-spin"
              />
              {$t('settings.cache.clearing')}
            {:else}
              <Icon icon="ph:check-duotone" class="w-4 h-4 mr-2" />
              {$t('settings.common.confirm')}
            {/if}
          </Button>
        {:else}
          <Button
            variant="soft-red"
            size="sm"
            rounded="full"
            onclick={() => (showConfirmation = true)}
            disabled={$isLoading}
          >
            <Icon icon="ph:trash-duotone" class="w-4 h-4 mr-2" />
            {$t('settings.cache.clear_cache')}
          </Button>
        {/if}
      </div>
    </SettingItem>
  </div>

  <div class="flex items-center justify-end gap-3 pt-4">
    {#if $isLoading}
      <div
        class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400"
      >
        <Loader variant="minimal" size="xs" />
        <span>{$t('settings.cache.clearing_cache')}</span>
      </div>
    {/if}
  </div>
</section>
