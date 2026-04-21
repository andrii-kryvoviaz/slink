<script lang="ts">
  import * as Share from '@slink/feature/Share';

  import type { PageData } from './$types';

  interface Props {
    data: PageData;
  }

  let { data }: Props = $props();
</script>

<Share.PasswordPrompt shareId={data.shareId} />
