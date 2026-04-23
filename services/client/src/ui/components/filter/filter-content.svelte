<script lang="ts">
  import { Command as CommandPrimitive } from 'bits-ui';
  import type { Snippet } from 'svelte';

  import { cn } from '@slink/utils/ui/index.js';

  import { getFilterSearchContext } from './context.svelte';
  import {
    type FilterScrollMaxHeight,
    filterScrollVariants,
  } from './filter.theme';

  interface Props {
    class?: string;
    maxHeight?: FilterScrollMaxHeight;
    children?: Snippet;
  }

  let { class: className, maxHeight = 'lg', children }: Props = $props();

  const search = getFilterSearchContext();

  const scrollClass = $derived(
    cn(filterScrollVariants({ maxHeight }), className),
  );
</script>

{#if search?.autocomplete}
  <CommandPrimitive.List class={scrollClass}>
    {@render children?.()}
  </CommandPrimitive.List>
{:else}
  <div class={scrollClass}>
    {@render children?.()}
  </div>
{/if}
