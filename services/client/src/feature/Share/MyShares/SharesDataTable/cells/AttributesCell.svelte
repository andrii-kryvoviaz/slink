<script lang="ts">
  import {
    ShareAttributes,
    ShareVariantBadges,
    hasVariantParams,
  } from '@slink/feature/Share';

  import type { ShareListItemResponse } from '@slink/api/Response/Share/ShareListItemResponse';

  interface Props {
    share: ShareListItemResponse;
  }

  let { share }: Props = $props();

  const hasVariant = $derived(hasVariantParams(share.variant));
  const hasAccess = $derived(
    share.requiresPassword || share.expiresAt !== null,
  );
</script>

<div class="flex items-center gap-1.5 flex-wrap">
  {#if hasVariant}
    <ShareVariantBadges variant={share.variant} />
  {/if}
  {#if hasAccess}
    <ShareAttributes
      requiresPassword={share.requiresPassword}
      expiresAt={share.expiresAt}
      isExpired={share.isExpired}
      emptyFallback={false}
    />
  {/if}
</div>
