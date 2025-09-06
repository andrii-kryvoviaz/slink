<script lang="ts">
  import { Button } from '@slink/ui/components/button';

  import Icon from '@iconify/svelte';

  interface Props {
    onclick?: () => void;
  }

  let { onclick }: Props = $props();
</script>

<Button
  variant="ghost"
  size="sm"
  {onclick}
  class="h-8 data-[state=open]:bg-blue-50 dark:data-[state=open]:bg-blue-900/20 data-[state=open]:text-blue-900 dark:data-[state=open]:text-blue-200"
>
  Email
  <Icon icon="heroicons:chevron-up-down" class="ml-2 size-4" />
</Button>
