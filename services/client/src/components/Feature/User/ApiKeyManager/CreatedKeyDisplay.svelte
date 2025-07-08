<script lang="ts">
  import Icon from '@iconify/svelte';

  import type { CreateApiKeyResponse } from '@slink/api/Resources/ApiKeyResource';

  import { Button } from '@slink/components/UI/Action';
  import { CopyContainer } from '@slink/components/UI/Action';

  interface Props {
    createdKey: CreateApiKeyResponse;
    isDownloadingConfig: boolean;
    onDownloadConfig: () => void;
    onClose: () => void;
  }

  let { createdKey, isDownloadingConfig, onDownloadConfig, onClose }: Props =
    $props();
</script>

<div class="space-y-6">
  <div class="flex items-center gap-4">
    <div
      class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-green-500 to-emerald-600 shadow-lg"
    >
      <Icon icon="lucide:check" class="h-6 w-6 text-white" />
    </div>
    <div>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
        Success!
      </h3>
      <p class="text-sm text-gray-500 dark:text-gray-400">
        Your API key has been created
      </p>
    </div>
  </div>

  <div
    class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border border-green-200 dark:border-green-800 rounded-2xl p-6"
  >
    <div class="flex items-center gap-3 mb-4">
      <Icon
        icon="lucide:shield-check"
        class="h-5 w-5 text-green-600 dark:text-green-400"
      />
      <span class="text-sm font-semibold text-green-800 dark:text-green-200">
        Your API Key
      </span>
    </div>
    <CopyContainer
      value={createdKey.key}
      placeholder="Your API key will appear here..."
      size="md"
      variant="success"
    />
  </div>

  <div
    class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4"
  >
    <div class="flex items-start gap-3">
      <Icon
        icon="lucide:alert-triangle"
        class="h-5 w-5 text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0"
      />
      <div>
        <h4 class="text-sm font-medium text-amber-900 dark:text-amber-100">
          Important Security Notice
        </h4>
        <p class="text-xs text-amber-700 dark:text-amber-300 mt-1">
          This key will not be shown again. Store it securely in a password
          manager or download the ShareX config file.
        </p>
      </div>
    </div>
  </div>

  <div class="flex gap-3">
    <Button
      variant="modern"
      onclick={onDownloadConfig}
      class="flex-1 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white border-0 shadow-lg hover:shadow-xl transition-all duration-200"
      disabled={isDownloadingConfig}
    >
      {#if isDownloadingConfig}
        <Icon icon="lucide:loader-2" class="h-4 w-4 mr-2 animate-spin" />
        Downloading...
      {:else}
        <Icon icon="lucide:download" class="h-4 w-4 mr-2" />
        Download ShareX Config
      {/if}
    </Button>
  </div>
</div>
