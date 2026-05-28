<script lang="ts">
  import type { VariantProps } from 'class-variance-authority';
  import type { Snippet } from 'svelte';

  import { cn } from '@slink/utils/ui/index.js';

  import { filterGroupVariants } from './filter.theme';

  type Variants = VariantProps<typeof filterGroupVariants>;

  interface Props {
    direction?: Variants['direction'];
    breakpoint?: Variants['breakpoint'];
    gap?: Variants['gap'];
    grow?: Variants['grow'];
    align?: Variants['align'];
    class?: string;
    children?: Snippet;
  }

  let {
    direction = 'row',
    breakpoint = 'lg',
    gap = 'md',
    grow = false,
    align = 'center',
    class: className,
    children,
  }: Props = $props();
</script>

<div
  class={cn(
    filterGroupVariants({ direction, breakpoint, gap, grow, align }),
    className,
  )}
>
  {@render children?.()}
</div>
