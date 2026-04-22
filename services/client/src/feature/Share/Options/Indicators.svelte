<script lang="ts">
  import ShareAttributes from '../Attributes/ShareAttributes.svelte';
  import { getShareControls } from '../State/Context';

  const share = getShareControls();

  const expiresAt = $derived.by<string | null>(() => {
    const date = share.expiration.date;
    return date ? date.toISOString() : null;
  });
</script>

<ShareAttributes
  requiresPassword={share.password.enabled}
  {expiresAt}
  isExpired={share.expiration.isExpired}
  emptyFallback={false}
/>
