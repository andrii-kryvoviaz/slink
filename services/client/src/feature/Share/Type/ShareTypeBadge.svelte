<script lang="ts">
  import { Badge } from '@slink/feature/Text';

  import Icon from '@iconify/svelte';

  import type { ShareableType } from '@slink/api/Response/Share/ShareResponse';

  interface Props {
    type: ShareableType;
  }

  let { type }: Props = $props();
</script>

{#if type === 'collection'}
  <Badge variant="purple" size="xs" class="gap-1 leading-none">
    <Icon icon="ph:folder-simple-duotone" class="h-3 w-3" />
    <span>Collection</span>
  </Badge>
{:else}
  <Badge variant="blue" size="xs" class="gap-1 leading-none">
    <Icon icon="ph:image-duotone" class="h-3 w-3" />
    <span>Image</span>
  </Badge>
{/if}
