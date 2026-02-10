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

  <div class="space-y-2">
    <span class="text-sm font-medium text-foreground/60">Your API Key</span>
    <CopyContainer
      value={createdKey.key}
      placeholder="Your API key will appear here..."
      size="md"
      variant="default"
    />
  </div>

  <Modal.Notice variant="warning">
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
        variant="primary"
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
    {/snippet}
  </Modal.Footer>
</div>
