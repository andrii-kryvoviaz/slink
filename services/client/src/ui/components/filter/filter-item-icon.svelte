<script lang="ts">
  import Icon from '@iconify/svelte';

  import { cn } from '@slink/utils/ui/index.js';

  import { getFilterContainerContext } from './context.svelte';
  import { filterItemIconVariants } from './filter.theme';

  interface Props {
    icon: string;
    class?: string;
  }

  let { icon, class: className }: Props = $props();

  const container = getFilterContainerContext();
</script>

<Icon
  {icon}
  class={cn(filterItemIconVariants({ variant: container.variant }), className)}
/>
