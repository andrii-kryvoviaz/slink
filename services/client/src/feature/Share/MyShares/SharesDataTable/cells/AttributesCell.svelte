<script lang="ts">
  import type { ShareListItemResponse } from '@slink/api/Response/Share/ShareListItemResponse';

  import ShareAttributes from '../../../Attributes/ShareAttributes.svelte';

  interface Props {
    share: ShareListItemResponse;
  }

  let { share }: Props = $props();
</script>

<ShareAttributes
  requiresPassword={share.requiresPassword}
  expiresAt={share.expiresAt}
  isExpired={share.isExpired}
/>
