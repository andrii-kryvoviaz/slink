<script lang="ts">
  import { ShareAttributeChips } from '@slink/feature/Share';

  import type { ShareListItemResponse } from '@slink/api/Response/Share/ShareListItemResponse';

  interface Props {
    share: ShareListItemResponse;
  }

  let { share }: Props = $props();
</script>

<ShareAttributeChips
  variant={share.variant}
  requiresPassword={share.requiresPassword}
/>
