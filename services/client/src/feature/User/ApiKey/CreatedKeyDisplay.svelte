<script lang="ts">
  import { CopyContainer } from '@slink/feature/Text';
  import { Button } from '@slink/ui/components/button';

  import Icon from '@iconify/svelte';

  import type { CreateApiKeyResponse } from '@slink/api/Resources/ApiKeyResource';

  interface Props {
    createdKey: CreateApiKeyResponse;
    isDownloadingConfig: boolean;
    onDownloadConfig: () => void;
    onClose: () => void;
  }

  let { createdKey, isDownloadingConfig, onDownloadConfig, onClose }: Props =
    $props();
</script>

<div class="space-y-5">
  <div class="flex items-center gap-3">
    <div
      class="w-10 h-10 rounded-xl bg-green-500/10 border border-green-500/10 flex items-center justify-center"
    >
      <Icon icon="ph:check" class="h-5 w-5 text-green-600" />
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
    class="bg-green-50/50 dark:bg-green-900/10 border border-green-200/50 dark:border-green-800/30 rounded-lg p-4"
  >
    <div class="flex items-center gap-3 mb-3">
      <Icon
        icon="ph:shield-check"
        class="h-4 w-4 text-green-600 dark:text-green-400"
      />
      <span class="text-sm font-medium text-green-800 dark:text-green-200">
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
    class="bg-amber-50/50 dark:bg-amber-900/10 border border-amber-200/50 dark:border-amber-800/30 rounded-lg p-3"
  >
    <div class="flex items-start gap-3">
      <Icon
        icon="ph:warning"
        class="h-4 w-4 text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0"
      />
      <div>
        <p class="text-xs text-amber-700 dark:text-amber-300">
          This key will not be shown again. Store it securely in a password
          manager or download the ShareX config file.
        </p>
      </div>
    </div>
  </div>

  <div class="flex gap-3">
    <Button
      variant="glass"
      size="sm"
      rounded="full"
      onclick={onClose}
      class="flex-1"
    >
      Close
    </Button>
    <Button
      variant="gradient-green"
      size="sm"
      rounded="full"
      onclick={onDownloadConfig}
      class="flex-1"
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
