<script lang="ts">
  import Icon from '@iconify/svelte';

  import { cn } from '@slink/utils/ui';

  import {
    type FilterIconContainerVariants,
    filterIconContainerVariants,
    filterIconVariants,
  } from './filter-bar.theme';

  interface Props {
    icon: string;
    variant?: FilterIconContainerVariants['variant'];
    spinning?: boolean;
    class?: string;
  }

  let {
    icon,
    variant = 'neon',
    spinning = false,
    class: className,
  }: Props = $props();
</script>

<div class={cn(filterIconContainerVariants({ variant }), className)}>
  <Icon
    {icon}
    class={cn(filterIconVariants({ variant }), spinning && 'animate-spin')}
  />
</div>
