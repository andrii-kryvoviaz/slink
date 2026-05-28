<script lang="ts">
  import Icon from '@iconify/svelte';

  import { cn } from '@slink/utils/ui/index.js';

  import {
    type FilterVariant,
    filterIconBoxVariants,
    filterIconGlyphVariants,
  } from './filter.theme';

  interface Props {
    icon: string;
    variant?: FilterVariant;
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

<div class={cn(filterIconBoxVariants({ variant }), className)}>
  <Icon
    {icon}
    class={cn(filterIconGlyphVariants({ variant }), spinning && 'animate-spin')}
  />
</div>
