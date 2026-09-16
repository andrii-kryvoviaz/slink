<script lang="ts">
  import type { Snippet } from 'svelte';

  import { cn } from '@slink/utils/ui/index.js';

  interface Props {
    id?: string;
    class?: string;
    title?: Snippet;
    description?: Snippet;
    children?: Snippet;
    body?: Snippet<[Snippet]>;
  }

  let {
    id,
    class: className,
    title,
    description,
    children,
    body,
  }: Props = $props();
</script>

{#snippet cards()}
  <div
    class="divide-y divide-muted rounded-xl bg-muted-soft/50 dark:bg-muted-soft/30 border border-muted overflow-hidden"
  >
    {@render children?.()}
  </div>
{/snippet}

<section {id} class={cn('space-y-1', className)}>
  <div class="flex items-center justify-between gap-4 pb-3">
    <div>
      {#if title}
        <h2
          class="text-sm font-medium text-foreground-muted uppercase tracking-wider"
        >
          {@render title?.()}
        </h2>
      {/if}
      {#if description}
        <p class="text-xs text-foreground-subtle mt-1">
          {@render description?.()}
        </p>
      {/if}
    </div>
  </div>

  {#if body}
    {@render body(cards)}
  {:else}
    {@render cards()}
  {/if}
</section>
