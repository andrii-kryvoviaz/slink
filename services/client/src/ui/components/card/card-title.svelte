<script lang="ts">
  import type { Snippet } from 'svelte';

  import { cn } from '@slink/utils/ui/index.js';

  import { type CardTitleVariants, cardTitleTheme } from './card.theme';

  type Props = CardTitleVariants & {
    as?: 'h2' | 'h3' | 'h4' | 'span';
    class?: string;
    children: Snippet;
  };

  let { as = 'h2', size, class: className, children }: Props = $props();
</script>

<svelte:element this={as} class={cn(cardTitleTheme({ size }), className)}>
  {@render children()}
</svelte:element>
