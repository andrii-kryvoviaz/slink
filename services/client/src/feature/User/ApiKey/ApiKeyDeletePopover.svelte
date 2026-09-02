<script lang="ts">
  import { Button } from '@slink/ui/components/button';

  import Icon from '@iconify/svelte';

  import type { ApiKeyResponse } from '@slink/api/Resources/ApiKeyResource';

  interface Props {
    apiKey: ApiKeyResponse;
    loading?: boolean;
    confirm: (apiKey: ApiKeyResponse) => void;
    onCancel?: () => void;
  }

  let { apiKey, loading = false, confirm, onCancel }: Props = $props();

  const handleConfirm = () => {
    confirm(apiKey);
  };
</script>

<div class="w-xs max-w-screen space-y-4">
  <div class="flex items-center gap-3">
    <div
      class="flex h-10 w-10 items-center justify-center rounded-full bg-danger/12 border border-danger-border/40 dark:border-danger-border/12 shadow-sm shrink-0"
    >
      <Icon icon="ph:key" class="h-5 w-5 text-danger" />
    </div>
    <div>
      <h3 class="text-sm font-semibold text-foreground">Revoke API Key</h3>
      <p class="text-xs text-foreground-muted">
        Key will be permanently revoked
      </p>
    </div>
  </div>

  <div class="bg-muted/50 rounded-xl p-4 border border-border/50">
    <div class="flex items-center gap-3">
      <div>
        <span class="text-sm font-medium text-foreground">
          {apiKey.name}
        </span>
        <p class="text-xs text-foreground-muted">
          This API key will be permanently revoked and cannot be recovered
        </p>
      </div>
    </div>
  </div>

  <div class="flex gap-3 pt-2">
    <Button
      variant="glass"
      rounded="full"
      size="sm"
      class="flex-1"
      disabled={loading}
      onclick={onCancel}
    >
      Cancel
    </Button>
    <Button
      variant="danger"
      rounded="full"
      size="sm"
      onclick={handleConfirm}
      justify="center"
      class="flex-1 font-medium shadow-lg hover:shadow-xl transition-all duration-200"
      {loading}
    >
      {#snippet leftIcon()}
        <Icon icon="ph:prohibit" class="h-4 w-4" />
      {/snippet}
      Revoke Key
    </Button>
  </div>
</div>
