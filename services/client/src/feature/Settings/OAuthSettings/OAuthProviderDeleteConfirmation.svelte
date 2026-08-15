<script lang="ts">
  import { ProviderIcon } from '@slink/feature/Auth';
  import { Button } from '@slink/ui/components/button';

  import Icon from '@iconify/svelte';

  import type { OAuthProviderDetails } from '@slink/api/Resources/OAuthResource';

  import type { OAuthProvider } from '@slink/lib/auth/oauth';

  interface Props {
    provider: OAuthProviderDetails;
    preset: OAuthProvider;
    loading: boolean;
    onConfirm: () => void;
    onCancel: () => void;
  }

  let { provider, preset, loading, onConfirm, onCancel }: Props = $props();
</script>

<div class="w-full max-w-sm p-2 space-y-4">
  <div class="flex items-center gap-3">
    <div
      class="flex h-10 w-10 items-center justify-center rounded-full bg-danger-wash dark:bg-danger-wash/20 border border-danger-border/40 dark:border-danger-border/12 shadow-sm flex-shrink-0"
    >
      <Icon icon="ph:shield-check" class="h-5 w-5 text-danger" />
    </div>
    <div>
      <h3 class="text-sm font-semibold text-foreground">Delete Provider</h3>
      <p class="text-xs text-foreground-muted">
        Provider and its configuration will be removed
      </p>
    </div>
  </div>

  <div class="bg-muted-soft/80 rounded-xl p-4 border border-border/50">
    <div class="flex items-center gap-3">
      <ProviderIcon provider={preset} class="h-5 w-5 flex-shrink-0" />
      <div class="min-w-0 flex-1">
        <span class="text-sm font-medium text-foreground">
          {provider.name}
        </span>
        <p class="text-xs text-foreground-muted">
          <code class="font-mono">{provider.slug}</code> &mdash; Provider and its
          configuration will be removed
        </p>
      </div>
    </div>
  </div>

  <div class="flex gap-3 pt-2">
    <Button
      variant="glass"
      rounded="full"
      size="sm"
      onclick={onCancel}
      class="flex-1"
      disabled={loading}
    >
      Cancel
    </Button>
    <Button
      variant="danger"
      rounded="full"
      size="sm"
      onclick={onConfirm}
      justify="center"
      class="flex-1 font-medium shadow-lg hover:shadow-xl transition-all duration-200"
      {loading}
    >
      {#snippet leftIcon()}
        <Icon icon="heroicons:trash" class="h-4 w-4" />
      {/snippet}
      Delete Provider
    </Button>
  </div>
</div>
