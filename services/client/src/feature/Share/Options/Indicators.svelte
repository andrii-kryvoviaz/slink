<script lang="ts">
  import { ButtonIcon } from '@slink/ui/components/button';

  import ShareAttributes from '../Attributes/ShareAttributes.svelte';
  import { getShareControls } from '../State/Context';

  const share = getShareControls();

  const expiresAt = $derived.by<string | null>(() => {
    const date = share.expiration.date;
    return date ? date.toISOString() : null;
  });
</script>

{#if share.isLoading}
  <span class="inline-flex h-4 w-4 items-center justify-center text-gray-400">
    <ButtonIcon loading />
  </span>
{:else}
  <ShareAttributes
    requiresPassword={share.password.enabled}
    {expiresAt}
    isExpired={share.expiration.isExpired}
    emptyFallback={false}
  />
{/if}
