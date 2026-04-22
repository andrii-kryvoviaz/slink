<script lang="ts">
  import type { Snippet } from 'svelte';

  import { cn } from '@slink/utils/ui';

  import {
    type FilterShellVariants,
    filterShellVariants,
  } from './filter-bar.theme';

  interface Props {
    variant?: FilterShellVariants['variant'];
    hasActiveSummary?: boolean;
    class?: string;
    children: Snippet;
  }

  let {
    variant = 'neon',
    hasActiveSummary = false,
    class: className,
    children,
  }: Props = $props();
</script>

<div class={cn(filterShellVariants({ variant, hasActiveSummary }), className)}>
  {@render children()}
</div>
