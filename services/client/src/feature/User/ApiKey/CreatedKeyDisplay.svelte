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

<div class="space-y-6">
  <div class="flex items-center gap-4">
    <div
      class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-500/10 to-emerald-600/15 dark:from-green-400/20 dark:to-emerald-500/25 border border-green-300/40 dark:border-green-600/50 flex items-center justify-center shadow-md backdrop-blur-sm"
    >
      <Icon
        icon="ph:check"
        class="h-6 w-6 text-green-700 dark:text-green-300 drop-shadow-sm"
      />
    </div>
    <div>
      <h3
        class="text-xl font-semibold text-slate-900 dark:text-white tracking-tight"
      >
        Success!
      </h3>
      <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
        Your API key has been created
      </p>
    </div>
  </div>

  <div
    class="bg-green-50/50 dark:bg-green-900/10 border border-green-200/50 dark:border-green-800/30 rounded-xl p-4"
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
    class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-50/90 via-white to-orange-50/80 dark:from-amber-950/20 dark:via-slate-800/50 dark:to-orange-950/30 border border-amber-200/40 dark:border-amber-800/30 p-5 shadow-lg backdrop-blur-sm"
  >
    <div
      class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-amber-100/30 via-transparent to-orange-100/20 dark:from-amber-900/20 dark:via-transparent dark:to-orange-900/10"
    ></div>
    <div class="relative flex items-start gap-4">
      <div class="flex-shrink-0 mt-0.5">
        <div
          class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg"
        >
          <Icon
            icon="ph:warning-duotone"
            class="h-5 w-5 text-white drop-shadow-sm"
          />
        </div>
      </div>
      <div class="flex-1 min-w-0">
        <h4
          class="text-sm font-semibold text-amber-900 dark:text-amber-100 leading-tight mb-2"
        >
          Important Notice
        </h4>
        <p
          class="text-sm text-amber-800/90 dark:text-amber-200/90 leading-relaxed"
        >
          This key will not be shown again. Store it securely in your password
          manager or download the ShareX config file below.
        </p>
      </div>
    </div>
  </div>

  <div class="flex gap-3 pt-2">
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
      class="flex-1 shadow-lg hover:shadow-xl transition-shadow duration-200"
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
