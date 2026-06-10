<script lang="ts">
  import type { Snippet } from 'svelte';

  import { fade } from 'svelte/transition';

  import { cn } from '@slink/utils/ui/index.js';

  import { type CardVariants, cardTheme } from './card.theme';

  type Props = CardVariants & {
    class?: string;
    fadeIn?: boolean;
    children: Snippet;
  };

  let {
    elevation,
    class: className,
    fadeIn = false,
    children,
  }: Props = $props();
</script>

{#if fadeIn}
  <div
    class={cn(cardTheme({ elevation }), className)}
    in:fade={{ duration: 300 }}
  >
    {@render children()}
  </div>
{:else}
  <div class={cn(cardTheme({ elevation }), className)}>
    {@render children()}
  </div>
{/if}
