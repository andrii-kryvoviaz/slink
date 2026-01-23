<script lang="ts">
  import { CopyContainer } from '@slink/feature/Text';
  import { Button } from '@slink/ui/components/button';
  import { Modal } from '@slink/ui/components/dialog';

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
  <Modal.Header>
    {#snippet icon()}
      <Icon icon="ph:check" />
    {/snippet}
    {#snippet title()}Success!{/snippet}
    {#snippet description()}Your API key has been created{/snippet}
  </Modal.Header>

  <div
    class="bg-green-50/50 dark:bg-green-900/10 border border-green-200/50 dark:border-green-800/30 rounded-xl p-4"
  >
    <div class="flex items-center gap-3 mb-3">
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

  <Modal.Notice variant="info">
    {#snippet icon()}
      <Icon icon="ph:warning-duotone" />
    {/snippet}
    {#snippet title()}Important Notice{/snippet}
    {#snippet message()}
      This key will not be shown again. Store it securely in your password
      manager or download the ShareX config file below.
    {/snippet}
  </Modal.Notice>

  <Modal.Footer>
    {#snippet actions()}
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
    {/snippet}
  </Modal.Footer>
</div>
