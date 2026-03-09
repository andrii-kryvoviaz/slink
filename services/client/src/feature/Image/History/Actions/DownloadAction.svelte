<script lang="ts">
  import { Button } from '@slink/ui/components/button';

  import Icon from '@iconify/svelte';

  interface Props {
    loading?: boolean;
    onAction: () => void;
  }

  let { loading = false, onAction }: Props = $props();
</script>

<Button
  variant="ghost"
  size="sm"
  rounded="full"
  class="gap-1.5"
  disabled={loading}
  onclick={onAction}
>
  <Icon icon="lucide:download" class="w-4 h-4" />
  <span class="hidden sm:inline">Download</span>
</Button>
